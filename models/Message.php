<?php namespace models;


class Message extends Model
{
    protected $table = 'messages';
    protected $attributes = ['conversation_id', 'sender_id', 'text', 'new', 'created_at'];
    protected $visible = ['conversation_id', 'sender_id', 'text', 'new', 'created_at'];

    public function getUserNick() {
    	$user = new User;
    	return $user->find($this->sender_id)->nick;
    }
}