<?php namespace controllers;

use models\User;
use lib\Auth;

class UserController extends Controller
{
    private $login_rules = [
        'email' => 'required:e-mailová adresa|email',
        'password' => 'required:heslo'
    ];

    private $register_rules = [
        'email' => 'required|email|unique:User:E-mailová adresa',
        'nick' => 'required|unique:User:Nick',
        'password' => 'required|min:5:Heslo|confirmed:Heslo:Heslá',
    ];

    public function login($request) {
        $validate = $this->validate($request, $this->login_rules);
        if ($validate->errors() == 0) {
            $attempt = Auth::login($request->email, $request->password);
            if ($attempt['result'] == 1) {
                return json_encode(['success' => 'Prihlásenie prebehlo úspešne!', 'user' => $attempt['user']]);
            } else {
                return json_encode(['errors' => ['Nesprávna e-mailová adresa alebo heslo!']]);
            }

        } else {
            return json_encode(['errors' => $validate->getErrors()]);
        }
    }

    public function register($request) {
        $validate = $this->validate($request, $this->register_rules);
        if ($validate->errors() == 0) {
            Auth::register($request->email, $request->nick, $request->password);
            return json_encode(['success' => 'Registrácia prebehla úspešne!', 'user' =>  Auth::get()->response()]);
        } else {
            return json_encode(['errors' => $validate->getErrors()]);
        }
    }

    public function logout() {
        return Auth::logout();
    }

    public function changePassword($request) {
        $validate = $this->validate($request, $this->register_rules);
        if ($validate->errors() == 0) {
            $user = Auth::get();
            if (!password_verify($request->old_password, $user->password) || !$user) {
                return json_encode(['errors' => ['Neplatné aktuálne heslo!']]);
            } else {
                $user->password = password_hash($request->password, PASSWORD_DEFAULT);
                $user->update();

                return json_encode(['success' => 'Heslo bolo úspešne zmenené!']);
            }
        } else {
            return json_encode(['errors' => $validate->getErrors()]);
        }
    }

    public function changeProfile($request) {
        $user = Auth::get();
        if ($user) {
            $user->profile = $request->profile;
            $user->update();

            return json_encode(['success' => 'Profil bol úspešne zmenený!']);
        }
        
        return json_encode(['error' => 'Musíte byť prihlásený!']);
    }

    public function profile($id) {
        $user_profile = new User;
        $user_profile = $user_profile->find($id);
        $user = Auth::get();
        $is_following = $user ? $user->isFollowing($user_profile->id) : 0;

        return $this->view('user', compact('user_profile', 'is_following'));
    }

    public function upload($request) {
        if (Auth::get()) {
            $target_dir = "uploads/";
            $target_file = '../public/img/' . basename($_FILES['file']['name']);
            $imageFileType = pathinfo($target_file, PATHINFO_EXTENSION);

            if (file_exists($target_file)) {
                return json_encode(['error' => 'Súbor už existuje!']);
            }

            if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
                return json_encode(['error' => 'Súbor musí mať koncovku jpg, png alebo gif!']);
            }

            if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
                $user = Auth::get();
                $user->avatar = $_FILES['file']['name'];
                $user->update();

                return json_encode(['success' => 'Avatar bol úspešne zmenený!', 'avatar' => $_FILES['file']['name']]);
            } else {
                return json_encode(['error' => 'Pri uploade nastala chyba!']);
            }
        }
    }

    public function followers($id) {
        $user_profile = new User;
        $user_profile = $user_profile->find($id);

        return $this->view('user-followers', compact('user_profile'));
    }

    public function following($id) {
        $user_profile = new User;
        $user_profile = $user_profile->find($id);

        return $this->view('user-following', compact('user_profile'));
    }

    public function follow($data) {
        $user = Auth::get();
        if ($user) {
            if ($data->type == 1) {
                if (!$user->isFollowing($data->id)) {
                    $user->follow($data->id);
                    return json_encode(['success' => 'Zapnutie sledovania bolo úspešné.']);
                } else {
                    return json_encode(['error' => 'Už sledujete tohto používateľa!']);
                }
            } else {
                if ($user->isFollowing($data->id)) {
                    $user->unfollow($data->id);
                    return json_encode(['success' => 'Zrušenie sledovania bolo úspešné.']);
                } else {
                    return json_encode(['error' => 'Najprv musíte sledovať tohto používateľa!']);
                }
            }
        }

        return json_encode(['error' => 'Musíte byť prihlásený!']);
    }

    public function users() {
        $users = new User;
        $users = $users->all();

        return $this->view('users', compact('users'));
    }
}