<?php namespace controllers;

use models\User;
use models\Message;
use models\Conversation;
use models\Notification;
use lib\Auth;

class MessageController extends Controller
{
    private $rules = [
        'title' => 'required:nadpis',
        'text' => 'required:text',
        'receiver' => 'required:príjemca'
    ];

	public function newMessage($id) {
		$nick = '';
		if ($id != 0) {
			$user = new User;
			$user = $user->find($id);
			if ($user) $nick = $user->nick;
		}

		return $this->view('new-message', compact('nick'));
	}

    public function create($request) {
        $validate = $this->validate($request, $this->rules);
        if ($validate->errors() == 0) {
            $sender = Auth::get();
            if ($sender) {
            	$receiver = new User;
            	$receiver = $receiver->where(['nick' => $request->receiver])->get();
            	if (count($receiver) > 0) {
            		$conversation = new Conversation;
            		$conversation->title = htmlspecialchars($request->title);
            		$conversation->sender_id = $sender->id;
            		$conversation->recipient_id = $receiver[0]->id;
            		$conversation->save();

            		$message = new Message;
	                $message->conversation_id = $conversation->id;
	                $message->sender_id = $sender->id;
	                $message->text = nl2br(htmlspecialchars($request->text));
	                $message->save();

	                $notification = new Notification;
	                $notification->text = 'Používateľ '.$sender->nick.' vám poslal novú správu!';
	                $notification->user_id = $receiver[0]->id;
	                $notification->link = 'sprava/' . $conversation->id;
	                $notification->save();

	                return json_encode(['success' => 'Správa bola úspešne odoslaná!']);
            	} else {
            		return json_encode(['errors' => ['Príjemca neexistuje!']]);
            	}
            }
        } else {
            return json_encode(['errors' => $validate->getErrors()]);
        }
    }

    public function message($id) {
    	$conversation = new Conversation;
    	$conversation = $conversation->find($id);
    	if ($conversation) {
    		if (!$conversation->hasAccess()) {
				$conversation = 0;
    		}
    	}

    	return $this->view('message', compact('conversation'));
    }

    public function reply($id) {
    	$conversation = new Conversation;
    	$conversation = $conversation->find($id);
    	if ($conversation && Auth::get()) {
    		if ($conversation->hasAccess()) {
				return $this->view('reply', compact('conversation'));
    		}
    	}

    	return $this->view('404');
    }

    public function update($request) {
    	$validate = $this->validate($request, $this->rules);
        if ($validate->errors() == 0 && Auth::get()) {
            $conversation = new Conversation;
            $conversation = $conversation->find((int)$request->id);
            if ($conversation) {
            	if ($conversation->hasAccess()) {
            		$conversation->updated_at = date('Y-m-d H:i:s');
            		$conversation->update();

            		$message = new Message;
	                $message->conversation_id = $conversation->id;
	                $message->sender_id = Auth::get()->id;
	                $message->text = nl2br(htmlspecialchars($request->text));
	                $message->save();

	                $notification = new Notification;
	                $notification->text = 'Používateľ '.Auth::get()->nick.' vám poslal novú správu!';
	                $notification->user_id = $conversation->getUserId();
	                $notification->link = 'sprava/' . $conversation->id;
	                $notification->save();

	                return json_encode(['success' => 'Správa bola úspešne odoslaná!']);
            	} else {
            		return json_encode(['errors' => ['Nemáte prístup!']]);
            	}
            }
        } else {
            return json_encode(['errors' => $validate->getErrors()]);
        }
    }
}