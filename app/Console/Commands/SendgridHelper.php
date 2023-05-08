<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\Log;
use SendGrid\Mail\From;
use SendGrid\Mail\Mail;
use SendGrid\Mail\To;
class SendgridHelper
{
    protected $templateId;
    protected $client;
    function init($tid, $key = null){
        if(empty($key)){
            $key = config('sendgrid.api_key');
        }
        $this->client = new \SendGrid($key);
        $this->setTemplateId($tid);
        dump("KEY:".$key);
    }

    function setTemplateId($tid){
        $this->templateId = $tid;
    }


    function setMailSetting($p, $i) {
        $email = null;
        try{
            $from = new From($p['from_email'], $p['from_name']);
            if(config('sendgrid.allow_public_email') == 1){
                $to = new To(trim($i["recipient"]), null, $i["meta"]);
            } else {
                var_dump("TEST RECIPIENT: ".config('sendgrid.testing_email'));
                $to = new To(config('sendgrid.testing_email'), null, $i["meta"]);
            }


            $email = new Mail(
                $from,
                $to
            );
            if(config('sendgrid.allow_public_email') == 1){
                $cc = [];
                if(array_key_exists("cc", $p) && $p["cc"])
                    $cc = explode('|',$p['cc']);
                if(array_key_exists("cc", $i) && $i["cc"]){
                    $cci = explode("|", $i["cc"]);
                    $cc1 = array_merge($cc, $cci);
                    $cc = array_unique($cc1);
                }
                $bcc = [];
                if(array_key_exists("bcc", $p) && $p["bcc"])
                    $bcc = explode('|',$p['bcc']);
                if(array_key_exists("bcc", $i) && $i["bcc"]){
                    $bcci = explode("|", $i["bcc"]);
                    $bcc1 = array_merge($bcc, $bcci);
                    $bcc = array_unique($bcc1);
                }
                foreach($cc as $key => $c){
                    if(!empty($c))
                        $email->addCc($c, "");
                }
                foreach($bcc as $key => $c){
                    if(!empty($c))
                        $email->addBcc($c, "");
                }

            }

            if(array_key_exists('reply_to', $p) && $p['reply_to'])
                $email->setReplyTo($p['reply_to']);

            $email->setTemplateId($this->templateId);
        } catch (\Exception $e){
            dump($i["recipient"]." ".$e->getMessage());
            Log::error($i["recipient"]." ".$e->getMessage());
        }

        return $email;
    }



    function send($email){
        if(!$email){
            dump("Email object is null cannot be sent.");
            return false;
        }
        $response = $this->client->send($email);
        //$status = $response->statusCode();
        //dump($response);
        $headers = $response->headers();
        //dump($headers);
        //dump($response->body());
        $messageId = null;
        foreach($headers as $h){
            if(str_contains($h, "X-Message-Id")){
                $value = explode(": ", $h);
                $messageId = $value[1];
                //dump($messageId);
                return $messageId;
            }
        }
    }

    function post($instances, $payloadDefault){
        if(empty($instances)) return false;
        try {
            $count = 0;
            $size = sizeof($instances);
            dump("Total instances is ".$size);

            foreach ($instances as $em) {
                $email = $this->setMailSetting($payloadDefault, $em);
                $messageId = $this->send($email);

                if (!$messageId) {
                    dump('no message id for' . $em['recipient']);
                    Log::error('no message id for' . $em['recipient']);
                } else {
                    dump($em['reference']." sent");
                }
                $count++;
                if($count % config('sendgrid.limit') === 0){//dump("sleep x seconds because of your limit");
                    sleep(config('sendgrid.sleep_time'));
                }
            }


        }
        catch (\Exception $e){
            Log::error("Exception: " . $e->getMessage() . "\n");

        }

    }
}
