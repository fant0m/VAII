<?php namespace controllers;

use models\Subject;
use models\User;
use models\Post;
use stdClass;

class BaseController extends Controller
{

    public function index() {
        return $this->view('layout');
    }

    public function notFound() {
        header('404 Not Found', true, 404);
        return $this->view('404');
    }

    public function partials($partial) {
        return $this->view($partial);
    }

    public function home() {
        $posts = new Post;
        $posts = $posts->all();

        return $this->view('home', compact('posts'));
    }

    public function test() {
        /*$data = [
            'email' => 'test@testtt.com',
            'nick' => 'hm'
        ];
        $user = new User($data);
        $user->password = 'k';
        $user->role = 'admin';
        $user->save();*/

        $users = new User;
        $users = $users->all();
        /*foreach ($users->all() as $user) {
            echo $user->getEmailNick()."<br>";
            echo $user->created_at."<br>";
            echo $user->email.'-'.$user->role."<br><br>";
        }*/

        /* $subject = new Subject();
         $subject = $subject->find(1);
         var_dump($subject);*/
//        $subject = new Subject();
//        $subject = $subject->find(1);


        $user = new User;
        $user = $user->find(25);
        $notifications = json($user->notifications());
        var_dump($notifications);
        foreach ($user->notifications() as $notification) {
//            var_dump($notification->json());
        }
        /*foreach ($user->subjects() as $subject) {
            echo $subject->name."<br>";
        }*/
        $title = 'Hello!';
        //$user = $users->find(1);
        /*$user->role = 'teacher';
        $user->save();

        $data = [
          'email' => 'k'
        ];
        $user->update($data);

        echo $user->email;
        echo $user->nick;
        echo $user->getEmailNick()."<br>";


        $user = $users->where('email', 'hodnota@google.sk')->first();
        echo $user->email;
*/

        $name = 'wutwutuw hahaha';

        $titlee = new stdClass();
        $titlee->name = 'wut?';
        $test = ['hm' => 'text'];
        $items = [
            0 => array(
                'example' => 'abc',
                'maybe' => 'haha'
            ),
            1 => array(
                'example' => 'def',
                'maybe' => 'huhu'
            )
        ];
        $list = ['wat', 'hm', 'k'];
        $objects = [];
        $objects[0] = new stdClass();
        $objects[1] = new stdClass();
        $objects[2] = new stdClass();
        $objects[3] = new stdClass();
        $objects[4] = new stdClass();
        $objects[5] = new stdClass();
        $objects[6] = new stdClass();
        $objects[7] = new stdClass();
        $objects[8] = new stdClass();
        $objects[9] = new stdClass();
        $objects[10] = new stdClass();
        $objects[11] = new stdClass();
        $objects[12] = new stdClass();
        $objects[13] = new stdClass();
        $objects[14] = new stdClass();
        $objects[15] = new stdClass();
        $objects[0]->name = 'a';
        $objects[0]->url = 'http://google.com';
        $objects[1]->name = 'b';
        $objects[1]->url = 'http://facebook.com';
        $objects[2]->name = 'c';
        $objects[2]->url = 'http://google.com';
        $objects[3]->name = 'd';
        $objects[3]->url = 'http://facebook.com';
        $objects[4]->name = 'e';
        $objects[4]->url = 'http://google.com';
        $objects[5]->name = 'f';
        $objects[5]->url = 'http://facebook.com';
        $objects[6]->name = 'g';
        $objects[6]->url = 'http://google.com';
        $objects[7]->name = 'h';
        $objects[7]->url = 'http://facebook.com';
        $objects[8]->name = 'i';
        $objects[8]->url = 'http://google.com';
        $objects[9]->name = 'j';
        $objects[9]->url = 'http://facebook.com';
        $objects[10]->name = 'k';
        $objects[10]->url = 'http://google.com';
        $objects[11]->name = 'l';
        $objects[11]->url = 'http://facebook.com';
        $objects[12]->name = 'm';
        $objects[12]->url = 'http://google.com';
        $objects[13]->name = 'n';
        $objects[13]->url = 'http://facebook.com';
        $objects[14]->name = 'o';
        $objects[14]->url = 'http://google.com';
        $objects[15]->name = 'p';
        $objects[15]->url = 'http://facebook.com';

//        return $this->view('test', compact('title', 'user', 'subject', 'list', 'users', 'items', 'objects', 'test', 'name'));
    }
}