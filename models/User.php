<?php namespace models;

class User extends Model
{
    protected $attributes = ['email', 'nick', 'password', 'avatar', 'profile'];
    protected $fillable = ['email', 'nick', 'profile', 'avatar'];
    protected $visible = ['id', 'email', 'password', 'nick', 'avatar', 'profile', 'created_at', 'updated_at'];
    protected $hidden = ['password'];

    public function getEmailNick() {
        return $this->email.' - '.$this->nick;
    }

    public function notifications() {
        return $this->hasMany('models\Notification');
    }

    public function newNotifications() {
        $result = [];
        foreach ($this->notifications() as $notification) {
            if ($notification->new) $result[] = $notification;
        }

        return $result;
    }

    public function posts() {
        return $this->hasMany('models\Post');
    }

    public function followers() {
        $sql = 'select a.* from users as a, followers as b where b.user_id = '.$this->id.' and a.id = b.follower_id';
        $query = $this->db->query($sql);

        return $query->fetchAll(\PDO::FETCH_CLASS, 'models\User');
    }

    public function following() {
        $sql = 'select a.* from users as a, followers as b where b.follower_id = '.$this->id.' and a.id = b.user_id';
        $query = $this->db->query($sql);

        return $query->fetchAll(\PDO::FETCH_CLASS, 'models\User');
    }

    public function countPosts() {
        return count($this->posts());
    }

    public function countFollowers() {
        return count($this->followers());
    }

    public function countFollowing() {
        return count($this->following());
    }

    public function formattedProfile() {
        return nl2br($this->profile);
    }

    public function isFollowing($id) {
        $sql = 'select count(*) as count from followers where user_id = '.$id.' and follower_id = '.$this->id;
        $query = $this->db->query($sql);

        return $query->fetch()['count'];
    }

    public function follow($id) {
        $sql = 'insert into followers (user_id, follower_id) values ('.(int)$id.', '.$this->id.')';
        $query = $this->db->query($sql);

        $notification = new Notification;
        $notification->text = 'Používateľ '.$this->nick.' vás začal sledovať!';
        $notification->user_id = (int)$id;
        $notification->link = 'uzivatel/' . $this->id;
        $notification->save();
    }

    public function unfollow($id) {
        $sql = 'delete from followers where user_id = '.(int)$id.' and follower_id = '.$this->id;
        $query = $this->db->query($sql);

        $notification = new Notification;
        $notification->text = 'Používateľ '.$this->nick.' vás prestal sledovať!';
        $notification->user_id = (int)$id;
        $notification->link = 'uzivatel/' . $this->id;
        $notification->save();
    }

    public function conversations() {
        $conversation = new Conversation;
        $sql = 'select '.$conversation->visible.' from '.$conversation->table.' where sender_id = '.$this->id.' or recipient_id = '.$this->id.' order by updated_at desc';
        $query = $this->db->query($sql);

        return $query->fetchAll(\PDO::FETCH_CLASS, 'models\Conversation');
    }

    public function countConversations() {
        return count($this->conversations());
    }
}