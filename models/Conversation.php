<?php namespace models;

use lib\Auth;

class Conversation extends Model
{
    public $table = 'conversations';
    protected $attributes = ['sender_id', 'recipient_id', 'title', 'created_at', 'updated_at'];
    public $visible = ['id', 'sender_id', 'recipient_id', 'title', 'created_at', 'updated_at'];

    public function messages() {
    	return $this->hasMany('models\Message');
    }

    public function getUserId() {
    	return Auth::getUserId() == $this->sender_id ? $this->recipient_id : $this->sender_id;
    }

    public function getUserNick() {
    	$user = new User;
    	return $user->find($this->getUserId())->nick;
    }

    public function hasAccess() {
    	return Auth::getUserId() == $this->sender_id || Auth::getUserId() == $this->recipient_id;
    }
}