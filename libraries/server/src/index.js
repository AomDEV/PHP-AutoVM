import Collection from 'xo-collection'
import createDebug from 'debug'
import filter from 'lodash.filter'
import forEach from 'lodash.foreach'
import isArray from 'lodash.isarray'
import isObject from 'lodash.isobject'
import kindOf from 'kindof'
import map from 'lodash.map'
import ms from 'ms'
import startsWith from 'lodash.startswith'
import { BaseError } from 'make-error'
import { EventEmitter } from 'events'
import {
  catchPlus as pCatch,
  delay as pDelay,
  promisify
} from 'promise-toolbox'
import {
  createClient as createXmlRpcClient,
  createSecureClient as createSecureXmlRpcClient
} from 'xmlrpc'

const debug = createDebug('xen-api')

// ===================================================================

function invoke (fn) {
  const n = arguments.length - 1
  if (!n) {
    return fn()
  }

  fn = arguments[n]
  const args = new Array(n)
  for (let i = 0; i < n; ++i) {
    args[i] = arguments[i]
  }

  return fn.apply(undefined, args)
}

// ===================================================================

// http://www.gnu.org/software/libc/manual/html_node/Error-Codes.html
const NETWORK_ERRORS = {
  // Connection has been closed outside of our control.
  ECONNRESET: true,

  // Connection has been aborted locally.
  ECONNABORTED: true,

  // Host is up but refuses connection (typically: no such service).
  ECONNREFUSED: true,

  // TODO: ??
  EINVAL: true,

  // Host is not reachable (does not respond).
  EHOSTUNREACH: true,

  // Connection configured timed out has been reach.
  ETIMEDOUT: true
}

const isNetworkError = ({code}) => NETWORK_ERRORS[code]

// -------------------------------------------------------------------

const XAPI_NETWORK_ERRORS = {
  HOST_STILL_BOOTING: true,
  HOST_HAS_NO_MANAGEMENT_IP: true
}

const isXapiNetworkError = ({code}) => XAPI_NETWORK_ERRORS[code]

// -------------------------------------------------------------------

const areEventsLost = ({code}) => code === 'EVENTS_LOST'

const isHostSlave = ({code}) => code === 'HOST_IS_SLAVE'

const isMethodUnknown = ({code}) => code === 'MESSAGE_METHOD_UNKNOWN'

const isSessionInvalid = ({code}) => code === 'SESSION_INVALID'

// -------------------------------------------------------------------

class XapiError extends BaseError {
  constructor ([code, ...params]) {
    super(`${code}(${params.join(', ')})`)

    this.code = code
    this.params = params
  }
}

export const wrapError = error => new XapiError(error)

// ===================================================================

const URL_RE = /^(?:(http(s)?:)\/*)?([^/]+?)(?::([0-9]+))?\/?$/
function parseUrl (url) {
  const matches = URL_RE.exec(url)
  if (!matches) {
    throw new Error('invalid URL: ' + url)
  }

  let [, protocol, isSecure, hostname, port] = matches
  if (!protocol) {
    protocol = 'https:'
    isSecure = true
  } else {
    isSecure = Boolean(isSecure)
  }

  return {
    isSecure,
    protocol, hostname, port,
    path: '/json',
    pathname: '/json'
  }
}

// -------------------------------------------------------------------

const SPECIAL_CHARS = {
  '\r': '\\r',
  '\t': '\\t'
}
const SPECIAL_CHARS_RE = new RegExp(
  Object.keys(SPECIAL_CHARS).join('|'),
  'g'
)

const parseResult = invoke(() => {
  const parseJson = JSON.parse

  return (result) => {
    const status = result.Status

    // Return the plain result if it does not have a valid XAPI
    // format.
    if (!status) {
      return result
    }

    if (status !== 'Success') {
      throw wrapError(result.ErrorDescription)
    }

    const value = result.Value

    // XAPI returns an empty string (invalid JSON) for an empty
    // result.
    if (!value) {
      return ''
    }

    try {
      return parseJson(value)
    } catch (error) {
      // XAPI JSON sometimes contains invalid characters.
      if (error instanceof SyntaxError) {
        let replaced
        const fixedValue = value.replace(SPECIAL_CHARS_RE, (match) => {
          replaced = true
          return SPECIAL_CHARS[match]
        })

        if (replaced) {
          return parseJson(fixedValue)
        }
      }

      throw error
    }
  }
})

// -------------------------------------------------------------------

const {
  create: createObject,
  defineProperties,
  defineProperty,
  freeze: freezeObject,
  prototype: { toString }
} = Object

const isString = invoke(toString.call(''), tag =>
  value => toString.call(value) === tag
)

// -------------------------------------------------------------------

const OPAQUE_REF_PREFIX = 'OpaqueRef:'
const isOpaqueRef = value => isString(value) && startsWith(value, OPAQUE_REF_PREFIX)

// -------------------------------------------------------------------

const isReadOnlyCall = invoke(/^[^.]+\.get_/, RE => (method, args) => (
  args.length === 1 &&
  isOpaqueRef(args[0]) &&
  RE.test(method)
))

// -------------------------------------------------------------------

const getKey = o => o.$id

// -------------------------------------------------------------------

const EMPTY_ARRAY = freezeObject([])

// ===================================================================

const MAX_TRIES = 5

const CONNECTED = 'connected'
const CONNECTING = 'connecting'
const DISCONNECTED = 'disconnected'

// -------------------------------------------------------------------

export class Xapi extends EventEmitter {
  constructor (opts) {
    super()

    this._auth = opts.auth
    this._pool = null
    this._readOnly = Boolean(opts.readOnly)
    this._sessionId = null
    this._url = parseUrl(opts.url)

    this._init()

    if (opts.watchEvents !== false) {
      this._debounce = opts.debounce == null
        ? 200
        : opts.debounce
      this._fromToken = ''

      // Memoize this function _addObject().
      this._getPool = () => this._pool

      const objects = this._objects = new Collection()
      objects.getKey = getKey

      this._objectsByRefs = createObject(null)
      this._objectsByRefs['OpaqueRef:NULL'] = null

      this.on('connected', this._watchEvents)
      this.on('disconnected', () => {
        this._fromToken = ''
        objects.clear()
      })
    }
  }

  get readOnly () {
    return this._readOnly
  }

  set readOnly (ro) {
    this._readOnly = Boolean(ro)
  }

  get sessionId () {
    const id = this._sessionId

    if (!id || id === CONNECTING) {
      throw new Error('sessionId is only available when connected')
    }

    return id
  }

  get status () {
    const id = this._sessionId

    return id
      ? (
        id === CONNECTING
          ? CONNECTING
          : CONNECTED
      )
      : DISCONNECTED
  }

  get _humanId () {
    return `${this._auth.user}@${this._url.hostname}`
  }

  connect () {
    const {status} = this

    if (status === CONNECTED) {
      return Promise.reject(new Error('already connected'))
    }

    if (status === CONNECTING) {
      return Promise.reject(new Error('already connecting'))
    }

    this._sessionId = 'connecting'

    return this._transportCall('session.login_with_password', [
      this._auth.user,
      this._auth.password
    ]).then(
      sessionId => {
        this._sessionId = sessionId

        debug('%s: connected', this._humanId)

        this.emit(CONNECTED)
      },
      error => {
        this._sessionId = null

        throw error
      }
    )
  }

  disconnect () {
    return Promise.resolve().then(() => {
      const { status } = this

      if (status === DISCONNECTED) {
        return Promise.reject(new Error('already disconnected'))
      }

      this._sessionId = null

      debug('%s: disconnected', this._humanId)

      this.emit(DISCONNECTED)
    })
  }

  // High level calls.
  call (method, ...args) {
    return this._readOnly && !isReadOnlyCall(method, args)
      ? Promise.reject(new Error(`cannot call ${method}() in read only mode`))
      : this._sessionCall(method, args)
  }

  // Nice getter which returns the object for a given $id (internal to
  // this lib), UUID (unique identifier that some objects have) or
  // opaque reference (internal to XAPI).
  getObject (idOrUuidOrRef, defaultValue) {
    const object = isString(idOrUuidOrRef)
      ? (
        // if there is an UUID, it is also the $id.
        this._objects.all[idOrUuidOrRef] ||
        this._objectsByRefs[idOrUuidOrRef]
      )
      : this._objects.all[idOrUuidOrRef.$id]

    if (object) return object

    if (arguments.length > 1) return defaultValue

    throw new Error('there is not object can be matched to ' + idOrUuidOrRef)
  }

  // Returns the object for a given opaque reference (internal to
  // XAPI).
  getObjectByRef (ref, defaultValue) {
    const object = this._objectsByRefs[ref]

    if (object) return object

    if (arguments.length > 1) return defaultValue

    throw new Error('there is no object with the ref ' + ref)
  }

  // Returns the object for a given UUID (unique identifier that some
  // objects have).
  getObjectByUuid (uuid, defaultValue) {
    // Objects ids are already UUIDs if they have one.
    const object = this._objects.all[uuid]

    if (object) return object

    if (arguments.length > 1) return defaultValue

    throw new Error('there is no object with the UUID ' + uuid)
  }

  get pool () {
    return this._pool
  }

  get objects () {
    return this._objects
  }

  // Medium level call: handle session errors.
  _sessionCall (method, args) {
    if (startsWith(method, 'session.')) {
      return Promise.reject(
        new Error('session.*() methods are disabled from this interface')
      )
    }

    return this._transportCall(method, [this.sessionId].concat(args))
    ::pCatch(isSessionInvalid, () => {
      // XAPI is sometimes reinitialized and sessions are lost.
      // Try to login again.
      debug('%s: the session has been reinitialized', this._humanId)

      this._sessionId = null
      return this.connect().then(() => this._sessionCall(method, args))
    })
  }

  // Low level call: handle transport errors.
  _transportCall (method, args, startTime = Date.now(), tries = 1) {
    return this._rawCall(method, args)
      ::pCatch(isNetworkError, isXapiNetworkError, error => {
        debug('%s: network error %s', this._humanId, error.code)

        if (!(tries < MAX_TRIES)) {
          debug('%s too many network errors (%s), give up', this._humanId, tries)

          throw error
        }

        // TODO: ability to cancel the connection
        // TODO: ability to force immediate reconnection
        // TODO: implement back-off

        return pDelay(5e3).then(() => {
          // TODO: handling not responding host.

          return this._transportCall(method, args, startTime, tries + 1)
        })
      })
      ::pCatch(isHostSlave, ({params: [master]}) => {
        debug('%s: host is slave, attempting to connect at %s', this._humanId, master)

        this._url.hostname = master
        this._init()

        return this._transportCall(method, args, startTime)
      })
      .then(
        result => {
          debug(
            '%s: %s(...) [%s] ==> %s',
            this._humanId,
            method,
            ms(Date.now() - startTime),
            kindOf(result)
          )
          return result
        },
        error => {
          debug(
            '%s: %s(...) [%s] =!> %s',
            this._humanId,
            method,
            ms(Date.now() - startTime),
            error
          )
          throw error
        }
      )
  }

  // Lowest level call: do not handle any errors.
  _rawCall (method, args) {
    return this._xmlRpcCall(method, args)
      .then(
        parseResult,
        error => {
          if (error.res) {
            console.error(
              'XML-RPC Error: %s (response status %s)',
              error.message,
              error.res.statusCode
            )
            console.error('%s', error.body)
          }

          throw error
        }
      )
  }

  _init () {
    const {isSecure, hostname, port, path} = this._url

    const client = (isSecure
      ? createSecureXmlRpcClient
      : createXmlRpcClient
    )({
      hostname,
      port,
      path,
      rejectUnauthorized: false,
      timeout: 10
    })

    this._xmlRpcCall = client.methodCall::promisify(client)
  }

  _addObject (type, ref, object) {
    const {_objectsByRefs: objectsByRefs} = this

    // Creates resolved properties.
    forEach(object, function resolveObject (value, key, object) {
      if (isArray(value)) {
        if (!value.length) {
          // If the array is empty, it isn't possible to be sure that
          // it is not supposed to contain links, therefore, in
          // benefice of the doubt, a resolved property is defined.
          defineProperty(object, '$' + key, {
            value: EMPTY_ARRAY
          })

          // Minor memory optimization, use the same empty array for
          // everyone.
          object[key] = EMPTY_ARRAY
        } else if (isOpaqueRef(value[0])) {
          // This is an array of refs.
          defineProperty(object, '$' + key, {
            get: () => freezeObject(map(value, (ref) => objectsByRefs[ref]))
          })

          freezeObject(value)
        }
      } else if (isObject(value)) {
        forEach(value, resolveObject)

        freezeObject(value)
      } else if (isOpaqueRef(value)) {
        defineProperty(object, '$' + key, {
          get: () => objectsByRefs[value]
        })
      }
    })

    // All custom properties are read-only and non enumerable.
    defineProperties(object, {
      $id: { value: object.uuid || ref },
      $pool: { get: this._getPool },
      $ref: { value: ref },
      $type: { value: type }
    })

    // Finally freezes the object.
    freezeObject(object)

    const objects = this._objects

    // An object's UUID can change during its life.
    const prev = objectsByRefs[ref]
    let prevUuid
    if (prev && (prevUuid = prev.uuid) && prevUuid !== object.uuid) {
      objects.remove(prevUuid)
    }

    this._objects.set(object)
    objectsByRefs[ref] = object

    if (type === 'pool') {
      this._pool = object
    }
  }

  _removeObject (ref) {
    const {_objectsByRefs: objectsByRefs} = this

    const object = objectsByRefs[ref]

    if (object) {
      this._objects.unset(object.$id)
      delete objectsByRefs[ref]
    }
  }

  _processEvents (events) {
    forEach(events, event => {
      const {operation: op} = event

      const {ref} = event
      if (op === 'del') {
        this._removeObject(ref)
      } else {
        this._addObject(event.class, ref, event.snapshot)
      }
    })
  }

  _watchEvents () {
    const loop = () => this._sessionCall('event.from', [
      ['*'],
      this._fromToken,
      1e3 + 0.1 // Force float.
    ]).then(onSuccess, onFailure)

    const onSuccess = ({token, events}) => {
      this._fromToken = token
      this._processEvents(events)

      const debounce = this._debounce
      return debounce != null
        ? pDelay(debounce).then(loop)
        : loop()
    }
    const onFailure = error => {
      if (areEventsLost(error)) {
        this._fromToken = ''
        this._objects.clear()

        return loop()
      }

      throw error
    }

    return loop()::pCatch(
      isMethodUnknown,

      // If the server failed, it is probably due to an excessively
      // large response.
      // Falling back to legacy events watch should be enough.
      error => error && error.res && error.res.statusCode === 500,

      () => this._watchEventsLegacy()
    )
  }

  // This method watches events using the legacy `event.next` XAPI
  // methods.
  //
  // It also has to manually get all objects first.
  _watchEventsLegacy () {
    const getAllObjects = () => {
      return this._sessionCall('system.listMethods', []).then(methods => {
        // Uses introspection to determine the methods to use to get
        // all objects.
        const getAllRecordsMethods = filter(
          methods,
          ::/\.get_all_records$/.test
        )

        return Promise.all(map(
          getAllRecordsMethods,
          method => this._sessionCall(method, []).then(
            objects => {
              const type = method.slice(0, method.indexOf('.')).toLowerCase()
              forEach(objects, (object, ref) => {
                this._addObject(type, ref, object)
              })
            },
            error => {
              if (error.code !== 'MESSAGE_REMOVED') {
                throw error
              }
            }
          )
        ))
      })
    }

    const watchEvents = () => this._sessionCall('event.register', [ ['*'] ]).then(loop)

    const loop = () => this._sessionCall('event.next', []).then(onSuccess, onFailure)

    const onSuccess = events => {
      this._processEvents(events)

      const debounce = this._debounce
      return debounce == null
        ? loop()
        : pDelay(debounce).then(loop)
    }

    const onFailure = error => {
      if (areEventsLost(error)) {
        return this._sessionCall('event.unregister', [ ['*'] ]).then(watchEvents)
      }

      throw error
    }

    return getAllObjects().then(watchEvents)
  }
}

// ===================================================================

// The default value is a factory function.
export const createClient = (opts) => new Xapi(opts)
