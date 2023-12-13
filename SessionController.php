<?php

class Session {
    public function __construct() {
        session_start();
    }

    public function set($key, $value) {
        $_SESSION[$key] = $value;
    }

    public function get($key) {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function delete($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy(){
        session_unset();
        session_destroy();
    }

    public function has($key) {
        return isset($_SESSION[$key]);
    }

    public function setMultiple(array $data) {
        $_SESSION = array_merge($_SESSION, $data);
    }
}

class Flash {
    private $session;

    public function __construct(Session $session) {
        $this->session = $session;
    }

    public function setMessage($message) {
        $this->session->set('flash_message', $message);
    }

    public function getMessage() {
        $message = $this->session->get('flash_message');
        
        if ($message) {
            // Очистим сообщение из сессии после получения
            $this->session->delete('flash_message');
            return $message;
        }

        return null;
    }
}
?>