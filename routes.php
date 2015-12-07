<?php

use lib\Router;

// redirect all routes to home
Router::get('/', 'BaseController');
Router::get('/prihlasenie', 'BaseController');
Router::get('/registracia', 'BaseController');
Router::get('/nastavenia', 'BaseController');
Router::get('/profil', 'BaseController');
Router::get('/kontakt', 'BaseController');
Router::get('/uzivatelia', 'BaseController');
Router::get('/uzivatel/:user', 'BaseController');
Router::get('/uzivatel/:user/prispevky', 'BaseController');
Router::get('/uzivatel/:user/sledovatelia', 'BaseController');
Router::get('/uzivatel/:user/sledujuci', 'BaseController');
Router::get('/uzivatel/:user/priatelia', 'BaseController');
Router::get('/moje-prispevky', 'BaseController');
Router::get('/prispevky-sledujuci', 'BaseController');
Router::get('/prispevok/:id', 'BaseController');
Router::get('/nova-sprava/:id', 'BaseController');
Router::get('/spravy', 'BaseController');
Router::get('/sprava/:id', 'BaseController');
Router::get('/odpovedat/:id', 'BaseController');


// redirect all angular's partials to the framework's views
Router::get('/partials/home', 'BaseController@home');
Router::get('/partials/users', 'UserController@users');
Router::get('/partials/user/:id', 'UserController@profile');
Router::get('/partials/posts/:id', 'PostController@posts');
Router::get('/partials/post/:id', 'PostController@post');

Router::get('/partials/followers-posts', 'PostController@fromFollowers');
Router::get('/partials/followers/:id', 'UserController@followers');
Router::get('/partials/following/:id', 'UserController@following');
Router::get('/partials/friends/:id', 'UserController@following');
Router::get('/partials/amessage/:id', 'MessageController@message');
Router::get('/partials/new-message/:id', 'MessageController@newMessage');
Router::get('/partials/reply/:id', 'MessageController@reply');
Router::get('/partials/:partial', 'BaseController@partials');


// api
Router::post('/login', 'UserController@login');
Router::post('/logout', 'UserController@logout');
Router::post('/register', 'UserController@register');
Router::post('/change-password', 'UserController@changePassword');
Router::post('/change-profile', 'UserController@changeProfile');
Router::post('/upload', 'UserController@upload');
Router::post('/follow/:id', 'UserController@follow');

Router::post('/notifications', 'NotificationController@get');
Router::post('/update-notifications', 'NotificationController@update');

Router::post('/post', 'PostController@create');

Router::post('/new-message', 'MessageController@create');
Router::post('/reply', 'MessageController@update');

// test
Router::get('/test', 'BaseController@test');