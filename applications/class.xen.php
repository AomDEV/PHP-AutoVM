<?php
/*
 *    PHP XenAPI v1.0
 *    a class for XenServer API calls
 *
 *    Copyright (C) 2010 Andy Goodwin <andyg@unf.net>
 *
 *    This class requires xml-rpc, PHP5, and curl.
 *
 *    Permission is hereby granted, free of charge, to any person obtaining
 *    a copy of this software and associated documentation files (the
 *    "Software"), to deal in the Software without restriction, including
 *    without limitation the rights to use, copy, modify, merge, publish,
 *    distribute, sublicense, and/or sell copies of the Software, and to
 *    permit persons to whom the Software is furnished to do so, subject to
 *    the following conditions:
 *
 *    The above copyright notice and this permission notice shall be included
 *    in all copies or substantial portions of the Software.
 *
 *    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 *    OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 *    MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
 *    IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY
 *    CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 *    TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
 *    SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */
namespace Xen;
use aomFramework\error;
use aomFramework\database;
use GuzzleHttp\Client as Client;

class xen {
    private $_url;
    private $_ip;
    private $_session_id;
    private $_user;
    private $_password;
    function __construct ($ip, $user, $password) {
        $url = "https://".$ip."/";
        $r = $this->xenrpc_request($url, $this->xenrpc_method('session.login_with_password', array($user, $password, '1.3.1')));
        if (is_array($r) && $r['Status'] == 'Success') {
            $this->_session_id = $r['Value'];
            $this->_url = $url;
            $this->_user = $user;
            $this->_password = $password;
            $this->_ip = $ip;
        } else {
          $er = new error();
          $er->fatal("0x004","Can't access to your VM Server!");
          exit();
        }
    }
    function __call($name, $args) {
        if (!is_array($args)) {
            $args = array();
        }
        list($mod, $method) = explode('__', $name, 2);
        $ret = $this->xenrpc_parseresponse($this->xenrpc_request($this->_url,
                  $this->xenrpc_method($mod . '.' . $method, array_merge(array($this->_session_id), $args))));
        return $ret;
    }
    function xenrpc_parseresponse($response,$is_echo=false) {
        if (!@is_array($response) && !@$response['Status']) {
          if($is_echo==true){
            echo "API failure.  (500)\n";  exit;
          } else{return array("status"=>false,"msg"=>"[500] API Failure");}
        } else {
            if ($response['Status'] == 'Success') {
               $ret = $response['Value'];
            } else {
               if ($response['ErrorDescription'][0] == 'SESSION_INVALID') {
                   $r = $this->xenrpc_request($url, $this->xenrpc_method('session.login_with_password',
                               array($this->_user, $this->_password, '1.3')));
                   if (!is_array($r) && $r['Status'] == 'Success') {
                       $this->_session_id = $r['Value'];
                   } else {
                     if($is_echo==true){
                       echo "API failure.  (session)\n";  exit;
                     } else{return array("status"=>false,"msg"=>"[session] API Failure");}
                   }
               } else {
                 if($is_echo==true){
                   echo "API failure.  (" . implode(' ', $response['ErrorDescription']) . ")\n";  exit;
                 } else{return array("status"=>false,"msg"=>"[".implode(' ', $response['ErrorDescription'])."] API Failure");}
               }
            }
        }
        return $ret;
    }
    function xenrpc_method($name, $params) {
        if($params==null){$params=array_merge(array($this->_session_id), array());}
        $ret = \xmlrpc_encode_request($name, $params);
        return $ret;
    }
    function xenrpc_request($url, $req) {
        $headers = array('Content-type: text/xml', 'Content-length: ' . strlen($req));
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
        $resp = curl_exec($ch);
        curl_close($ch);
        $ret = xmlrpc_decode($resp);
        return $ret;
    }
    function xmlParse($xml){
      return new \SimpleXMLElement($xml);
    }

    private $vmId;

    /**
    * Set VMID
    * @param
    * @return String
    **/
    function setVMID($vmHostName){
      $getVMID = @$this->VM__get_by_name_label($vmHostName)[0];
      if(isset($getVMID)){
        $this->vmId=$getVMID;
        return $getVMID;
      } else{ return false; }
    }

    /**
    * Get Session ID
    * @param
    * @return String
    **/
    function getSessionID(){
      return $this->_session_id;
    }

    /**
    * Get VMID
    * @param
    * @return String VMID
    **/
    function getVMID(){
      return $this->vmId;
    }

    /**
    * Get UUID from VMID
    * @param
    * @return String UUID
    **/
    function getUUID(){
      return $this->VM__get_uuid($this->getVMID());
    }

    /**
    * Get XEN Server Username
    * @param
    * @return String
    **/
    function getUsername(){
      return $this->_user;
    }

    /**
    * Get XEN Server Password
    * @param
    * @return String
    **/
    function getPassword(){
      return $this->_password;
    }

    /**
    * Get XEN Server URL/IP
    * @param
    * @return String
    **/
    function getIP(){
      return $this->_ip;
    }

    /**
    * Get Guest Metrics from VMID and return with arrays record
    * @param
    * @return Arrays
    **/
    function getGuestMetrics(){
      $VMG = $this->VM__get_guest_metrics($this->getVMID());
      return $this->VM_guest_metrics__get_record($VMG);
    }

    /**
    * Get Metrics from VMID and return with arrays record
    * @param
    * @return Arrays
    **/
    function getMetrics(){
      $VMG = $this->VM__get_metrics($this->getVMID());
      return $this->VM_metrics__get_record($VMG);
    }

    /**
    * Get VM Power State (Running,Halted,Suspended)
    * @param
    * @return Arrays
    **/
    function getPowerState(){
      $powerState = $this->VM__get_power_state($this->getVMID());
      return $powerState;
    }

    function getVMRRD($parse=true){
      $vmURL = "http://{$this->getUsername()}:{$this->getPassword()}@{$this->getIP()}/vm_rrd?uuid={$this->getUUID()}&start=1";
      $client = new Client();
      $request = $client->get($vmURL);
      $body = $request->getBody();
      if($parse==true){
        return $this->xmlParse($body);
      } else{
        return $body;
      }
    }

    function getRRDUpdates($parse=true){
      $vmURL = "http://{$this->getUsername()}:{$this->getPassword()}@{$this->getIP()}/rrd_updates?start=1";
      $client = new Client();
      $request = $client->get($vmURL);
      $body = $request->getBody();
      if($parse==true){
        return $this->xmlParse($body);
      } else{
        return $body;
      }
    }

    function getVMRRDValue($name,$vm_rrd=null){
      if($vm_rrd==null){
        $rrd = $this->getVMRRD();
      } else{
        $rrd = $vm_rrd;
      }

      $value = null;
      foreach($rrd->ds as $ds){
        if($ds->name == $name){
          $value = $ds->value;
        }
      }
      return $value;
    }

    function getVMRecord(){
      $record = $this->VM__get_record($this->getVMID());
      return $record;
    }

    function getMemoryUsage(){
      if($this->getPowerState() == "Running"){
        $rrd = $this->getVMRRD();
        $total_memory = $this->getVMRRDValue('memory',$rrd);
        $free_memory  = $this->getVMRRDValue('memory_internal_free',$rrd);
        $percentage = round(($total_memory - ($free_memory*1000))/$total_memory*100);
        return $percentage;
      } else{
        return 1;
      }
    }

    function getDiskUsage(){
      if($this->getPowerState() == "Running"){
        $vbd = $this->VBD__get_all();
        $sum = 0;
        $max = 0;
        $i = 0;
        $getVMRRD = $this->getVMRRD();
        foreach($vbd as $v){
          $device = $this->VBD__get_device($v);
          $getValue = $this->getVMRRDValue("vbd_{$device}_read",$getVMRRD) + $this->getVMRRDValue("vbd_{$device}_write",$getVMRRD);
          $sum += $getValue;
          if ($getValue > $max) $max = $getValue;
          $i++;
        }
        $avg = ceil($sum / (1000 * $i));
        $max = ceil($max/1000);
        $msg = $avg." / ".$max." KBs";
        return $msg;
      } else{
        return "0 / 0 KBs";
      }
    }

    function createVM($templateName,$new_hostname,$vm_cpu=1,$vm_ram=512){
      # List all the VM objects
      $vms = $this->VM__get_all_records();

      $templates = array();
      foreach($vms as $record){
        $ty = "VM";
        if($record["is_a_template"]){
          $ty = "Template";
          # Look for a debian template
          if( preg_match("/^".$templateName."$/i",$record["name_label"]) ){
            $templates[]=($record["name_label"]);
          }
        }
      }

      # Choosing a template to clone
      if(!is_array($templates)){
        $return = array("msg"=>"Not found VM Template","status"=>false);
      } else{

        #===============
        $template = @$templates[0];
        $this->setVMID($template);
        $getTemplateID = $this->getVMID();

        $newVM = $this->VM__clone($getTemplateID,$new_hostname);
        # Set CPU
        $cpu = intval($vm_cpu);
        $this->VM__set_VCPUs_max($newVM,$cpu);
        $this->VM__set_VCPUs_at_startup($newVM,$cpu);
        # Set CPU

        # Set memory
        $memory = intval($vm_ram)*1024; //RAM in Bytes
        $this->VM__set_memory_limits($newVM,$memory,$memory,$memory,$memory);
        # Set memory

        # Provision VM
        $this->VM__provision($newVM);
        # Provision VM

        # Start VM
        $this->VM__start($newVM,false,true);
        # Start VM

        # Check error
        $vm_name = json_encode($newVM);
        if(preg_match("/There is insufficient space/i",$vm_name)){
          return array("msg"=>"There is insufficient space","status"=>false);
        } else{
          return array("msg"=>"Successful!","status"=>true,"vm"=>$newVM);
        }
      }
    }

    function getVMIP(){
      $this->setVMID($this->getVMID());
      $ipRef = @$this->getGuestMetrics();
      $vmIP = @$ipRef['networks']['0/ip'];
      return $vmIP;
    }

    function getCPUUsage(){
      if($this->getPowerState() == "Running"){
        $sum = 0;
        $VMG = $this->VM__get_metrics($this->getVMID());
        $total = $this->VM_metrics__get_VCPUs_number($VMG);
        $getVMRRD = $this->getVMRRD();
        $i=0;
        for ($i=0;$i<$total;$i++){
          $cpuValue = (float) $this->getVMRRDValue("cpu{$i}",$getVMRRD);
          $sum += $cpuValue;
        }
        @$percentage = ceil($sum*100/$total);
        return $percentage;
      } else{
        return 1;
      }
    }

    function destroy($test_mode=false){
      $vbdRefList = $this->VM__get_VBDs($this->getVMID());
      foreach ($vbdRefList as $vbdRef)
      {
        $vbd = $this->VBD__get_record($vbdRef);
        $vdiRef = $vbd['VDI'];
        $vdi = $this->VDI__get_record($vdiRef);
        if($vdi["name_label"]=="xs-tools.iso"){
          // This is Xen tools OS
        } else{
          $this->VDI__destroy($vdiRef);
        }
      }
      return $this->VM__destroy($this->getVMID());
    }

    function getNetworkUUID($bridge="xenbr0",$return_all=false){
      $network_list = $this->network__get_all_records();
      $getNetwork = null;
      foreach($network_list as $network){
        if($getNetwork==null and $network['bridge']==$bridge){
          $getNetwork = $network;
        }
      }
      if($return_all==true){
        return $getNetwork;
      } else{ return $getNetwork["uuid"]; }
    }

    function getVIF(){
      $vifs = $this->VIF__get_all();
      foreach($vifs as $vif){
        $vm = $this->VIF__get_VM($vif);
        if($vm==$this->getVMID()){ return $vif; }
      }
    }

}
