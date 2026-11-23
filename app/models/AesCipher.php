<?php
define("OPENSSL_CIPHER_NAME", "aes-128-cbc");
define("CIPHER_KEY_LEN", 16);
define("initVector", "abcdef9876543210");
define("keyForEncryption", "LW4DRWimSecK1082");
//local
//define("baseURL", "http://localhost/rahul/RestoWiseLicense/API/");
//server
define("baseURL", "http://restowise.com/license/API/");
//define("baseURL", "http://localhost/RestoWiseLicense/API/");
define("checkLicenseURL", "registervarify");


class AesCipher {
  
      
    
   /*private const   OPENSSL_CIPHER_NAME = "aes-128-cbc";
    
    private   const   CIPHER_KEY_LEN = 16; //128 bits
    public   const  initVector = "abcdef9876543210"; // 16 bytes IV, it is recommended to use a different random IV for every message!
    public  const    keyForEncryption = "LW4DRWimSecK1082";*/
   static function fixKey($key) {
        
        if (strlen($key) < CIPHER_KEY_LEN) {
            //0 pad to len 16
            return str_pad("$key", CIPHER_KEY_LEN, "0"); 
        }
        
        if (strlen($key) > CIPHER_KEY_LEN) {
            //truncate to 16 bytes
            return substr($str, 0, CIPHER_KEY_LEN); 
        }

        return $key;
    }

/*
 private static function fixKey($key) {
        
        if (strlen($key) < AesCipher::CIPHER_KEY_LEN) {
            //0 pad to len 16
            return str_pad("$key", AesCipher::CIPHER_KEY_LEN, "0"); 
        }
        
        if (strlen($key) > AesCipher::CIPHER_KEY_LEN) {
            //truncate to 16 bytes
            return substr($str, 0, AesCipher::CIPHER_KEY_LEN); 
        }

        return $key;
    }
*/
    /**
    * Encrypt data using AES Cipher (CBC) with 128 bit key
    * 
    * @param type $key - key to use should be 16 bytes long (128 bits)
    * @param type $iv - initialization vector
    * @param type $data - data to encrypt
    * @return encrypted data in base64 encoding with iv attached at end after a :
    */
    static function encrypt($data) {
        $key=keyForEncryption;
        $iv=initVector;

        $encodedEncryptedData = base64_encode(openssl_encrypt($data, OPENSSL_CIPHER_NAME, AesCipher::fixKey($key), OPENSSL_RAW_DATA, $iv));
        $encodedIV = base64_encode($iv);
        $encryptedPayload = $encodedEncryptedData.":".$encodedIV;

        return $encryptedPayload;
    }

    /*
     static function encrypt($data) {
        $key=AesCipher::keyForEncryption;
        $iv=AesCipher::initVector;

        $encodedEncryptedData = base64_encode(openssl_encrypt($data, AesCipher::OPENSSL_CIPHER_NAME, AesCipher::fixKey($key), OPENSSL_RAW_DATA, $iv));
        $encodedIV = base64_encode($iv);
        $encryptedPayload = $encodedEncryptedData.":".$encodedIV;

        return $encryptedPayload;
    }*/

 
     
      

    /**
    * Decrypt data using AES Cipher (CBC) with 128 bit key
    * 
    * @param type $key - key to use should be 16 bytes long (128 bits)
    * @param type $data - data to be decrypted in base64 encoding with iv attached at the end after a :
    * @return decrypted data
    */
     static function decrypt($data) {
        try {
            
            $key=keyForEncryption;
        $parts = explode(':', $data);
        if(sizeof($parts)>1)
        {
            $encrypted = $parts[0];

        $iv = $parts[1];
       /* if(strlen($iv)>16)
        {
           return FALSE;
        }*/
        $decryptedData = openssl_decrypt(base64_decode($encrypted), OPENSSL_CIPHER_NAME, AesCipher::fixKey($key), OPENSSL_RAW_DATA, base64_decode($iv));
        return $decryptedData;

        }
        else
        {
            return FALSE;
        }
        
        } catch (Exception $e) {
            return FALSE;
        }
        
    }

    /*
     static function decrypt($data) {
        $key=AesCipher::keyForEncryption;
        $parts = explode(':', $data); //Separate Encrypted data from iv.
        $encrypted = $parts[0];
        $iv = $parts[1];
        $decryptedData = openssl_decrypt(base64_decode($encrypted), AesCipher::OPENSSL_CIPHER_NAME, AesCipher::fixKey($key), OPENSSL_RAW_DATA, base64_decode($iv));
        return $decryptedData;
    }
    */
     static function checkFile()
    {
        
        return file_exists("./utils/lw.lnc");
    }

    static function checkAll($product_type='LWLodging',$is_varify_only=TRUE,$licData=NULL)
    {

        $data = array();
        if( (file_exists("./utils/lw.lnc") && filesize("./utils/lw.lnc")>0) || $licData)
        {
            try
             {
            if($licData)
            {
                 

               $result=$licData; 
            }
            else{
               
               $myfile = fopen("./utils/lw.lnc", "r");
                $content=fread($myfile,filesize("./utils/lw.lnc"));
                //echo "con ".$content;
               $result= AesCipher::decrypt($content);

               //echo json_encode(AesCipher::csv_to_array($result));
                 


            }    
           
             if($result)
             {
                 //echo "Result ".$result;
                 //give CURL Call
                 if(AesCipher::checkLicense($result))
                 {
                    $data=AesCipher::getLicenseData($result);
                    $hwid=AesCipher::getMotherBoardSerial().' '.AesCipher::getCPUSerialNo();
                    //print_r($data);
                    $product_key=$data['product_key'];
                    if(empty(strpos($data['lictype'],'TRIAL')))
                    {
                         $data['success']=FALSE;
                         $data['message']="Invalid License";
                       //  echo "data ".json_encode($data);
                         //die;
                         return $data;
                    }
                    else
                    {
                      $lictypes='';
                        $arrTrial=explode(",", $data['lictype']);
                        
                        foreach ($arrTrial as $val) {
                            
                              $val=trim(str_replace('"', '', $val));
                              //echo "val ".$val;
                              if(strpos($val, "TRIAL")!==FALSE)
                              { 
                                // echo " tt ".json_encode($val); 
                                 $arrDays=explode("-", $val);
                                 //echo "noo ".json_encode($arrDays);
                                 $noofdays=$arrDays[1];
                                 break;
                              }
                              else
                              {
                                $lictypes.=$val.",";
                              }
                        }
                      
                    }
                  
                    if(strpos(strtolower($lictypes), strtolower($product_type))===FALSE)
                    {
                       $data['success']=FALSE;
                       $data['message']='Invalid license';
                       
                       return $data;
                    }
                    //$is_varify_only="true";
                    if($is_varify_only)
                    {
               

                      $post = [
                       'is_varify_only' => "true",
                       'hwid' => $hwid,
                       'pid'   => $product_key
                        ];

                        if(file_exists("./utils/dt.lnc"))
                        {
                           $myfile = fopen("./utils/dt.lnc", "r");
                           $fileStr= fgets($myfile);
                           $expDate=AesCipher::decrypt($fileStr);
                           $today=date('Y-m-d H:i:s');

                           //echo "today ".$expDate;die;
                          
                           if($today > $expDate)
                           {
                              $data['success']=FALSE;
                          $data['message']='Invalid license';
                        //echo "expDate ".$expDate;die;
                          return $data;

                           }
                           else
                           {
                            //echo "expDate w ".$expDate;die;
                              $data['success']=TRUE;
                              $data['message']='Valid license';
                              
                              return $data;
                           }
                        }
                        else
                        {
                          $data['success']=FALSE;
                          $data['message']='Invalid license';
                       
                          return $data;

                        }


                       // echo "post ".json_encode($post);die;
                    }
                    else
                    {
                        $Today=date('y:m:d');
                        $NewDate=Date('y:m:d', strtotime('+3 days'));
                         $post = [
                       'is_varify_only' => $is_varify_only,
                       'hwid' => $hwid,
                       'pid'   => $product_key,
                       'expiry_date'=>''
                       ];  
                       

                    }

                     
                   //echo "    ".json_encode($post);die;
                    if(AesCipher::isConnected())
                    {
                        //echo " int ";
                        $result=AesCipher::checkFromServer($post);
                        //echo " ".json_encode($result);die;
                        if($result)
                        {

                          $result=json_decode($result);
                          $data['success']=$result->success;
                          $data['message']=$result->msg;
                          $data['lic']=$result->lic;

                        }
                        else
                        {
                          $data['success']=FALSE;
                          $data['message']="Some error occurred.";
                        }
                        
                        return $data;       
                    }
                    else
                    {
                        //echo " no int ";
                        $data['success']=TRUE;
                        $data['message']="Valid";
                        return $data; 
                    }
                    
                   
                 }
                 else
                 {
                    $data['success']=FALSE;
                    $data['message']='Invalid license,Please upload valid license';
                    return $data;
                 }
               
             }
             else
             {


                 $data['success']=FALSE;
                 $data['message']="Invalid license";
                 return $data;

             }
            

            } catch (Exception $e) {
                 $data['success']=FALSE;
                 $data['message']='Invalid License';
                 return $data;
                
            }
             
        }
        else
        {
            $data['success']=FALSE;
            $data['message']='Invalid License';
            return $data;
        }
    }
     static function getCPUSerialNo () {
        $return_arry = array (); exec("WMIC CPU get Processorid", $return_arry,$val); 
        $cpu_sn = $return_arry[1]; return $cpu_sn;
        } 

        static function getMotherBoardSerial() {$return_arry = array (); exec("WMIC baseboard get SerialNumber", $return_arry); $bASEBOARD_SN = $return_arry[1]; $bASEBOARD_SN = Str_replace ("-", "", $bASEBOARD_SN);
          return $bASEBOARD_SN;
       }//Remove the characters in the string "-" return $baseboard _SN; }}

        static function checkLicense($value)
       {
           if(empty(trim($value)))
           {
             return FALSE;
           }
           else
           {
              if(empty(strpos($value,'gst#')) || 
                 empty(strpos($value,'com#')) || 
                 empty(strpos($value,'fss#')) || 
                 empty(strpos($value,'email#')) || 
                 empty(strpos($value,'mno#')) || 
                 empty(strpos($value,'lictype#')) ||
                 empty(strpos($value,'noofterminals#')) ||
                 empty(strpos($value,'id#')) ||
                 empty(strpos($value,'product_key#')) ||
                 empty(strpos($value,'product#')) ||
                 empty(strpos($value,'add#')) || 
                 empty(strpos($value,'biller#'))  

               ) 
                       
              {
                return FALSE;
               
              }
              else
              {

                return TRUE;
              }
           }
        }

     static function getLicenseData($value)
       {
       // echo "VALUE ".$value;
           $data = array();
           if(empty(trim($value)))
           {
             return FALSE;
           }
           else
           {

              $arr=explode(",", $value);
             // echo "dtaaa ".json_encode($arr);
              foreach ($arr as $val) {
                
                  $arr1=explode("#", $val);

                  if(sizeof($arr1)>1)
                  {

                    $data[str_replace('"', '', $arr1[0])]=str_replace('"', '', $arr1[1]);    
                  }
                  else
                  {
                    $data[str_replace('"', '', $arr1[0])]="";  
                  }
                  
              }
              $v1=strpos($value,"lictype");
              $v2=strpos($value,'"',$v1);
              $str=substr($value,$v1,($v2-$v1));
              $arrLicTypes=explode("#", $str);
              if(sizeof($arrLicTypes)>1)
              {
                $data['lictype']=$arrLicTypes[1];  
              }
              else
              {
                $data['lictype']='';   
              }
              

              //echo "  LIC ".json_encode($data);
              return $data;
             
           }
        }

         static function checkFromServer($post)
        {
           $target_url=baseURL.''.checkLicenseURL; 
           $ch = curl_init();
           curl_setopt($ch, CURLOPT_URL,$target_url);
           curl_setopt($ch, CURLOPT_POST,1);
           curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           $result=curl_exec($ch);
           return $result;
            # code...
        }

  static  function isConnected()
    {
    
            $connected = @fsockopen("www.google.com", 80);
            if ($connected){
                fclose($connected);
                return true; 
            }
            return false;
    }


    static function register($product_type='LWLodging',$licData=NULL,$varify_only=FALSE)
    {

        $data = array();

        if($licData)
        {
            try
             {
             
              $result=AesCipher::decrypt($licData); 
                   

             if($result)
             {
                 //echo "Result ".$result;
                 //give CURL Call
                 if(AesCipher::checkLicense($result))
                 {
                    $data=AesCipher::getLicenseData($result);
                    $hwid=AesCipher::getMotherBoardSerial().' '.AesCipher::getCPUSerialNo();
                    $product_key=$data['product_key'];

                    $noofdays=0;
                    $lictypes="";
                    if(empty(strpos($data['lictype'],'TRIAL')))
                    {
                         $data['success']=FALSE;
                         $data['message']="Invalid License";
                       //  echo "data ".json_encode($data);
                         //die;
                         return $data;
                    }
                    else
                    {

                        $arrTrial=explode(",", $data['lictype']);
                        
                        foreach ($arrTrial as $val) {
                            
                              $val=trim(str_replace('"', '', $val));
                              //echo "val ".$val;
                              if(strpos($val, "TRIAL")!==FALSE)
                              { 
                                // echo " tt ".json_encode($val); 
                                 $arrDays=explode("-", $val);
                                 //echo "noo ".json_encode($arrDays);
                                 $noofdays=$arrDays[1];
                                 break;
                              }
                              else
                              {
                                $lictypes.=$val.",";
                              }
                        }
                      
                    }
                  
                    if(strpos(strtolower($lictypes), strtolower($product_type))===FALSE)
                    {
                       $data['success']=FALSE;
                       $data['message']='Invalid license';
                       
                       return $data;
                    }
                  // echo "string true ".$lictypes;die;
                      date_default_timezone_set('Asia/Kolkata');   
                        $date = new DateTime("now", new DateTimeZone('Asia/Kolkata') );
             
                        $expiry_date=date('Y-m-d H:i:s', strtotime('+'.$noofdays.' days',$date->getTimestamp()));
                         $post = [
                       'is_varify_only' => FALSE,
                       'hwid' => $hwid,
                       'pid'   => $product_key,
                       'expiry_date'=>$expiry_date
                       ];  

                      // echo "data ".json_encode($post);
                    // die;
                    if(1==1)//AesCipher::isConnected()
                    {
                        //echo " int ";

                     //   $ci=&get_instance();

                      // $ci->load->model("settings_model");
           
                      // echo "".json_encode($result);die();
                        $result=AesCipher::checkFromServer($post);  
                     echo " ".json_encode($result);
                       $v=json_decode($result);
                       echo "".$v->expiry_date;
                       //die();

                        Sma::updateSetting(array('default_curr' => AesCipher::encrypt($v->expiry_date),'default_dt'=>$licData) );
                        if($result)
                        {
                          $result1=$result;
                          $result=json_decode($result);
                          $result2=($result->lic);
                          $data['success']=$result->success;
                          $data['message']=$result->msg;
                          $data['expiry_date']=$expiry_date;
                          //add to registary
                             $data['reg_success']=FALSE;
                    try 
                        {
                             $Wshshell = new COM('WScript.Shell');
                         
                             $Wshshell->regWrite("HKEY_LOCAL_MACHINE\SOFTWARE\WOW6432Node\lw\PosWise\lic",$result2->license_str);

                             $Wshshell->regWrite("HKEY_LOCAL_MACHINE\SOFTWARE\WOW6432Node\lw\PosWise\exp",$result2->expiry_date);
                             $data['reg_success']=TRUE;
                        } 
                  catch (Exception $e) {
                             $data['reg_success']=FALSE;
                        }
         




                          
                        }
                        else
                        {
                          $data['success']=FALSE;
                          $data['message']='Some error occurred';
                        }  
                    }
                    else
                    {
                          $data['success']=FALSE;
                          $data['message']='No Internet';
                    }
                    
                   
                    return $data;   
                  }
                 else
                 {
                    $data['success']=FALSE;
                    $data['message']='Invalid license,Please upload valid license';
                    return $data;
                 }
               
             }
             else
             {


                 $data['success']=FALSE;
                 $data['message']=AesCipher::getCPUSerialNo();
                 return $data;

             }
            

            } catch (Exception $e) {
                 $data['success']=FALSE;
                 $data['message']='Invalid License';
                 return $data;
                
            }
             
        }
        else
        {
            $data['success']=FALSE;
            $data['message']=AesCipher::getMotherBoardSerial();
             return $data;
        }
    }

  static function checkLicenseDetails($product_type)
    {
        $data = array();
        if( (file_exists("./utils/lw.lnc") && filesize("./utils/lw.lnc")>0))
        {
            try
             {
           
                $myfile = fopen("./utils/lw.lnc", "r");
                $content=fread($myfile,filesize("./utils/lw.lnc"));
                $result= AesCipher::decrypt($content);
              
           
             if($result)
              {

                 if(AesCipher::checkLicense($result))
                 {
                    $data=AesCipher::getLicenseData($result);
                    $hwid=AesCipher::getMotherBoardSerial().' '.AesCipher::getCPUSerialNo();
                    //print_r($data);
                    $product_key=$data['product_key'];
                    $arrData=AesCipher::getLicenseTypesAndNoOfDays($data['lictype']);
                   //  $lictypesNoOfDays = array('noofdays' =>$noofdays ,'lictypes'=>$lictypes);
                     if(!empty($lictypesNoOfDays))
                     {
                        if(!empty($lictypesNoOfDays['lictypes']) && !empty($lictypesNoOfDays['noofdays']))
                          {
                              if(strpos($lictypesNoOfDays['lictypes'], $product_type)===FALSE)
                                {
                                   $data['success']=FALSE;
                                   $data['message']='Invalid license';
                                   return $data;
                                }
                          }
                          else
                          {
                                $data['success']=FALSE;
                                $data['message']='Invalid License';
                                return $data;
                          }
                     }
                     else
                     {
                          $data['success']=FALSE;
                          $data['message']='Invalid License';
                     }
                    

                      $post = [
                       'is_varify_only' => "true",
                       'hwid' => $hwid,
                       'pid'   => $product_key
                        ];
                   

                    if(AesCipher::isConnected())
                    {
                        //echo " int ";
                        $result=AesCipher::checkFromServer($post);
                        //echo " ".json_encode($result);die;
                        if($result)
                        {

                          $result=json_decode($result);
                          $data['success']=$result->success;
                          $data['message']=$result->msg;

                        }
                        else
                        {
                          $data['success']=FALSE;
                          $data['message']="Some error occurred.";
                        }
                        
                        return $data;       
                    }
                    else
                    {
                        //echo " no int ";
                        $data['success']=TRUE;
                        $data['message']="Valid";
                        return $data; 
                    }
                    
                   
                 }
                 else
                 {
                    $data['success']=FALSE;
                    $data['message']='Invalid license,Please upload valid license';
                    return $data;
                 }
               
             }
             else
             {


                 $data['success']=FALSE;
                 $data['message']="Invalid license";
                 return $data;

             }
            

            } catch (Exception $e) {
                 $data['success']=FALSE;
                 $data['message']='Invalid License';
                 return $data;
                
            }
             
        }
        else
        {
            $data['success']=FALSE;
            $data['message']='License Not Found or Invalid License';
            return $data;
        }
    }

     static function getLicenseTypesAndNoOfDays($lictypeArr)
    {
            $noofdays=0;
            $lictypes="";
            $lictypesNoOfDays = array('noofdays' =>$noofdays ,'lictypes'=>$lictypes);
            if($lictypeArr)
            {
              $arrTrial=explode(",", $data['lictype']);
                        
                        foreach ($arrTrial as $val) {
                            
                              $val=trim(str_replace('"', '', $val));
                              //echo "val ".$val;
                              if(strpos($val, "TRIAL")!==FALSE)
                              { 
                                // echo " tt ".json_encode($val); 
                                 $arrDays=explode("-", $val);
                                 //echo "noo ".json_encode($arrDays);
                                 $noofdays=$arrDays[1];
                                 break;
                              }
                              else
                              {
                                $lictypes.=$val.",";
                              }
                        }
                 $lictypesNoOfDays = array('noofdays' =>$noofdays ,'lictypes'=>$lictypes);       
            }
             return $lictypesNoOfDays;
     }

     static function csv_to_array($filename='', $delimiter=',', $enclosure='"', $escapestring='"')
     {
          if(!file_exists($filename) || !is_readable($filename))
              return FALSE;

          $data = array();
          if (($handle = fopen($filename, 'r')) !== FALSE) {
              while (($row = fgetcsv($handle, 1000, $delimiter, $enclosure, $escapestring)) !== FALSE) {
                  $data[] = $row;
              }
              fclose($handle);
          }
          return $data;
    }

    static function getLicData()
    {
       $dt=Sma::getSettings();
    
       $result=AesCipher::decrypt($dt->default_dt); 

       $data=AesCipher::getLicenseData($result);
      // echo json_encode($data); 
       return $data;

    }
};
?>