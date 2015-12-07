<?php namespace models;


class Notification extends Model
{
    protected $table = 'notifications';
    protected $attributes = ['text', 'link', 'new'];
    protected $visible = ['id', 'user_id', 'text', 'link', 'new'];
}