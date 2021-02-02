<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class IndexController extends Controller {

    public function index(Request $request) {
        $oauth_token = $request->session()->get('oauth_token');
        if($oauth_token) {
            return view('welcome');
        }
        return view('login');
    }

    public function login() {
        return view('login');
    }
    
    public function auth(Request $request) {
        \Codebird\Codebird::setConsumerKey(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'));
        $cb = \Codebird\Codebird::getInstance();

        if($request->oauth_verifier && $request->oauth_token) {
            $cb->setToken($request->session()->get('oauth_token'), $request->session()->get('oauth_token_secret'));
            $reply = $cb->oauth_accessToken([
                'oauth_verifier' => $request->oauth_verifier,
                'oauth_token ' => session('oauth_token')
            ]);
            $request->session()->put('oauth_token', $reply->oauth_token);
            $request->session()->put('oauth_token_secret', $reply->oauth_token_secret);

            return redirect()->to('/');
        }

        $oauth_token = $request->session()->get('oauth_token');
        if(!$oauth_token) {
            $reply = $cb->oauth_requestToken([
                'oauth_callback' => 'http://twitterupload.site/auth'
            ]);

            $cb->setToken($reply->oauth_token, $reply->oauth_token_secret);
            $request->session()->put('oauth_token', $reply->oauth_token);
            $request->session()->put('oauth_token_secret', $reply->oauth_token_secret);
            $request->session()->put('oauth_verify', true);
            $request->session()->save();
            $auth_url = $cb->oauth_authorize();

            header('Location: ' . $auth_url);
            die();
        }

        return view('welcome');
    }

    public function createTweet(Request $request) {
        \Codebird\Codebird::setConsumerKey(env('TWITTER_CONSUMER_KEY'), env('TWITTER_CONSUMER_SECRET'));
        $cb = \Codebird\Codebird::getInstance();

        $cb->setToken($request->session()->get('oauth_token'), $request->session()->get('oauth_token_secret'));

        $params['status'] = $request->tweet;
        if($request->media) {
            $path = \Illuminate\Support\Facades\Storage::disk('public')->put('media', $request->media);
            $path = public_path('/') . $path;
//        var_dump(public_path('/') . $path);exit;
            $mimeType = mime_content_type($path);
            $fileType = explode('/', $mimeType)[0];
            if($fileType == 'video') {
                $size_bytes = filesize($path);
                $fp         = fopen($path, 'r');
                $reply = $cb->media_upload([
                    'command'     => 'INIT',
                    'media_type'  => 'video/mp4',
                    'total_bytes' => $size_bytes
                ]);
                $media_id = $reply->media_id_string;
                $segment_id = 0;
                while (! feof($fp)) {
                    $chunk = fread($fp, 1048576); // 1MB per chunk for this sample
                    $reply = $cb->media_upload([
                        'command'       => 'APPEND',
                        'media_id'      => $media_id,
                        'segment_index' => $segment_id,
                        'media'         => $chunk
                    ]);
                    $segment_id++;
                }
                fclose($fp);
                $reply = $cb->media_upload([
                    'command'       => 'FINALIZE',
                    'media_id'      => $media_id
                ]);
            }else {
                $reply = $cb->media_upload([
                    'media' => $path
                ]);
            }
            $params['media_ids'] = $reply->media_id_string;
        }
        $cb->statuses_update($params);
    }
    
}