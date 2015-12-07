<?php namespace controllers;

use models\Post;
use models\Notification;
use models\User;
use lib\Auth;

class PostController extends Controller
{
    private $rules = [
        'title' => 'required:nadpis',
        'text' => 'required:text',
        'type' => 'required:typ|values:0,1,2,3,4,5:typ',
        'privacy' => 'required:súkromie|values:0,1:súkromie'
    ];

    public function create($request) {
        $validate = $this->validate($request, $this->rules);
        if ($validate->errors() == 0) {
            $user = Auth::get();
            if ($user) {
                $post = new Post($request);
                $post->user_id = Auth::getUserId();
                $post->text = nl2br($post->text);
                $post->update();

                foreach ($user->followers() as $follower) {
                    $notification = new Notification;
                    $notification->text = 'Používateľ '.$user->nick.' pridal nový príspevok s nadpisom '.$post->title.'!';
                    $notification->user_id = $follower->id;
                    $notification->link = 'prispevok/' . $post->id;
                    $notification->save();
                }

                return json_encode(['success' => 'Hejt bol úspešne pridaný!']);
            }
        } else {
            return json_encode(['errors' => $validate->getErrors()]);
        }
    }

    public function fromFollowers() {
        $user = Auth::get();
        $posts = [];
        $count_posts = 0;

        if ($user) {
            $followers = $user->following();
            $ids = [];
            foreach ($followers as $follower) {
                $ids[] = $follower->id;
            }

            if (count($ids) > 0) {
                $ids = implode(',', $ids);
                $post = new Post;
                $posts = $post->in($ids);
                $count_posts = count($posts);
            }
        }

        return $this->view('followers-posts', compact('posts', 'count_posts'));
    }

    public function posts($id) {
        $user_profile = new User;
        $user_profile = $user_profile->find($id);

        return $this->view('user-posts', compact('user_profile'));
    }

    public function post($id) {
        $post = new Post;
        $post = $post->find($id);
        $wut = $post->user();

        return $this->view('post', compact('post', 'wut'));
    }
}