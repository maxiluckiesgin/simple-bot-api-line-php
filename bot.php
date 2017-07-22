<?php

/*
  copyright @ medantechno.com
  2017

 */

require_once('./line_class.php');
require_once('./unirest-php-master/src/Unirest.php');

$channelAccessToken = ''; //sesuaikan
$channelSecret = ''; //sesuaikan

$client = new LINEBotTiny($channelAccessToken, $channelSecret);

//var_dump($client->parseEvents());
//$_SESSION['userId']=$client->parseEvents()[0]['source']['userId'];

/*
  {
  "replyToken": "nHuyWiB7yP5Zw52FIkcQobQuGDXCTA",
  "type": "message",
  "timestamp": 1462629479859,
  "source": {
  "type": "user",
  "userId": "U206d25c2ea6bd87c17655609a1c37cb8"
  },
  "message": {
  "id": "325708",
  "type": "text",
  "text": "Hello, world"
  }
  }
 */


$userId = $client->parseEvents()[0]['source']['userId'];
$groupId = $client->parseEvents()[0]['source']['groupId'];
$replyToken = $client->parseEvents()[0]['replyToken'];
$timestamp = $client->parseEvents()[0]['timestamp'];
$type = $client->parseEvents()[0]['type'];

$message = $client->parseEvents()[0]['message'];
$messageid = $client->parseEvents()[0]['message']['id'];

$profil = $client->profil($userId);

$pesan_datang = explode(" ", $message['text']);

$command = $pesan_datang[0];
$options = $pesan_datang[1];
if (count($pesan_datang) > 2) {
    for ($i = 2; $i < count($pesan_datang); $i++) {
        $options .= '+';
        $options .= $pesan_datang[$i];
    }
}

function vid_search($keyword) {
    //kx1y0tJcXeU
    $uri = 'http://genyoutube.com/watch?v=' . $keyword;

    $response = Unirest\Request::get("$uri");
    $gre = array();
    $hasil = str_replace(">", "&gt;", $response->raw_body);
    $arrays = explode("<", $hasil);
    $grep = preg_grep('/&mime=video%2Fmp4/i', $arrays);
    foreach ($grep as $gr) {
        array_push($gre, $gr);
    }

    $result = explode('"', $gre[1])[3];
    return (string) (Unirest\Request::get("$result")->headers['Location']);
    //return $result;
}

function img_search($keyword) {
    $uri = 'https://www.google.co.id/search?q=' . $keyword . '&safe=off&source=lnms&tbm=isch';

    $response = Unirest\Request::get("$uri");

    $hasil = str_replace(">", "&gt;", $response->raw_body);
    $arrays = explode("<", $hasil);
    return explode('"', $arrays[291])[3];
}

function getWifiID(&$client, $ID) {
    $wait = array(
        'to' => $ID,
        'messages' => array(
            array(
                'type' => 'text',
                'text' => 'Tunggu ya, memang lama...'
            )
    ));
    $client->pushMessage($wait);

    $headers = array('Accept' => '*/*');
$data = array('voc' => '');
$body = Unirest\Request\Body::form($data);

$response = Unirest\Request::post('http://widpedia.net/a/voucher/V0C.php', $headers, $body);
    $body_ = "Voucher \n\n".$response->body;
    $res = array(
        'to' => $ID,
        'messages' => array(
            array(
                'type' => 'text',
                'text' => $response->code
            ),
            array(
                'type' => 'text',
                'text' => $body_
            )
    ));
    $client->pushMessage($res);
}

function anime($keyword) {

    $fullurl = 'https://myanimelist.net/api/anime/search.xml?q=' . $keyword;
    $username = 'buntutkadal';
    $password = 'FZQYeZ6CE9is';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_URL, $fullurl);

    $returned = curl_exec($ch);
    $xml = new SimpleXMLElement($returned);
    $parsed = array();

    $parsed['id'] = (string) $xml->entry[0]->id;
    $parsed['image'] = (string) $xml->entry[0]->image;
    $parsed['title'] = (string) $xml->entry[0]->title;
    $parsed['desc'] = "Episodes : ";
    $parsed['desc'] .= $xml->entry[0]->episodes;
    $parsed['desc'] .= "\nScore : ";
    $parsed['desc'] .= $xml->entry[0]->score;
    $parsed['desc'] .= "\nType : ";
    $parsed['desc'] .= $xml->entry[0]->type;
    $parsed['synopsis'] = str_replace("<br />", "\n", html_entity_decode((string) $xml->entry[0]->synopsis, ENT_QUOTES | ENT_XHTML, 'UTF-8'));
    return $parsed;
}

function anime_syn($title) {
    $parsed = anime($title);
    $result = "Title : " . $parsed['title'];
    $result .= "\n\nSynopsis :\n" . $parsed['synopsis'];
    return $result;
}

function urb_dict($keyword) {
    $uri = "http://api.urbandictionary.com/v0/define?term=" . $keyword;

    $response = Unirest\Request::get("$uri");


    $json = json_decode($response->raw_body, true);
    $result = $json['list'][0]['definition'];
    $result .= "\n\nExamples : \n";
    $result .= $json['list'][0]['example'];
    return $result;
}

//show menu, saat join dan command #/menu
if ($type == 'join' || $command == '#/menu') {
    $text = "mbeek (´▽｀)\nHere how to use me\n\n";
    $text .= "1.#/def [keyword] - define something\n";
    $text .= "2.#/img [keyword] - search image for something\n";
    $text .= "3.#/anime [keyword] - search for an anime from MAL\n";
    $text .= "4.#/anime-syn [keyword] - read an anime synopsis from MAL\n";
    $text .= "5.#/goaway - I will leave this group";
    $balas = array(
        'replyToken' => $replyToken,
        'messages' => array(
            array(
                'type' => 'text',
                'text' => $text
            )
        )
    );
}

//pesan bergambar
if ($message['type'] == 'text') {
    if ($command == '#/def') {


        $balas = array(
            'replyToken' => $replyToken,
            'messages' => array(
                array(
                    'type' => 'text',
                    'text' => 'Definition : ' . urb_dict($options)
                )
            )
        );
    } else if ($command == '#/goaway') {
$push = array(
							'to' => $groupId,									
							'messages' => array(
								array(
										'type' => 'text',					
										'text' => '􀂣'
									)
							)
						);
						
		
		$client->pushMessage($push);
$push = array(
							'to' => $groupId,									
							'messages' => array(
								array(
										'type' => 'text',					
										'text' => 'here is my poo...'
									)
							)
						);
						
		
		$client->pushMessage($push);

        $psn = $client->leaveGroup($groupId);
    } else if ($command == '#/mauvoucher') {
        $ID = $userId;
        if ($groupId != "") {
            $ID = $groupId;
        }
        getWifiID($client, $ID);
    } else if ($command == '#/img') {
        $hasil = img_search($options);
        $balas = array(
            'replyToken' => $replyToken,
            'messages' => array(
                array(
                    'type' => 'image',
                    'originalContentUrl' => $hasil,
                    'previewImageUrl' => $hasil
                )
            )
        );
    } else if ($command == '#/pika') {
        $keyword = 'Zl_ZeIMHWjc';
        $image = 'https://img.youtube.com/vi/' . $keyword . '/2.jpg';
        $balas = array(
            'replyToken' => $replyToken,
            'messages' => array(
                array(
                    'type' => 'image',
                    'originalContentUrl' => $image,
                    'previewImageUrl' => $image
                ), array(
                    'type' => 'video',
                    'originalContentUrl' => vid_search($keyword),
                    'previewImageUrl' => $image
                )
            )
        );
    } else if ($command == '#/anime') {
        $result = anime($options);
        $altText = "Title : " . $result['title'];
        $altText .= "\n\n" . $result['desc'];
        $altText .= "\nMAL Page : https://myanimelist.net/anime/" . $result['id'];
        $balas = array(
            'replyToken' => $replyToken,
            'messages' => array(
                array(
                    'type' => 'template',
                    'altText' => $altText,
                    'template' => array(
                        'type' => 'buttons',
                        'title' => $result['title'],
                        'thumbnailImageUrl' => $result['image'],
                        'text' => $result['desc'],
                        'actions' => array(
                            array(
                                'type' => 'postback',
                                'label' => 'Read Synopsis',
                                'data' => 'action=add&itemid=123',
                                'text' => '#/anime-syn ' . $options
                            ),
                            array(
                                'type' => 'uri',
                                'label' => 'Myanimelist Page',
                                'uri' => 'https://myanimelist.net/anime/' . $result['id']
                            )
                        )
                    )
                )
            )
        );
    } else if ($command == '#/anime-syn') {

        $result = anime_syn($options);
        $balas = array(
            'replyToken' => $replyToken,
            'messages' => array(
                array(
                    'type' => 'text',
                    'text' => $result
                )
            )
        );
    }
}
if (isset($balas)) {
    $result = json_encode($balas);
//$result = ob_get_clean();

    file_put_contents('./balasan.json', $result);


    $client->replyMessage($balas);
}?>
