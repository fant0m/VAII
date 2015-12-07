<?php namespace lib;

use models\User;

class Auth
{
    public static function register($email, $nick, $password) {
        $user = new User;
        $user->email = $email;
        $user->nick = $nick;
        $user->password = password_hash($password, PASSWORD_DEFAULT);
        $user->save();

        return $_SESSION['user'] = $user->id;
    }

    public static function login($email, $password) {
        $user = new User;
        $user = $user->where(['email' => $email])->get();
        if (count($user) == 1) {
            if (password_verify($password, $user[0]->password)) {
                $_SESSION['user'] = $user[0]->id;

                return ['result' => self::check(), 'user' => self::get()->response()];
            }
        }

        return ['result' => self::check()];
    }

    public static function logout() {
        session_destroy();
        unset($_SESSION['user']);
    }

    public static function check() {
        return (int)isset($_SESSION['user']);
    }

    public static function get() {
        if (!self::check()) {
            return 0;
        } else {
            $user = new User;
            $user = $user->find($_SESSION['user']);
            $user->notifications = json($user->newNotifications());

            return $user;
        }
    }

    public static function getUserId() {
        return self::get()->id;
    }
}