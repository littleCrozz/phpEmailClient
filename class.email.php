<?php

class Email_reader {

    // imap server connection
    public $conn;
    // inbox storage and inbox message count
    private $inbox;
    private $msg_cnt;
    // email login credentials
    private $server = 'mail.domain.com';
    private $user = 'email@domain.com';
    private $pass = 'password';
    private $port = "143/novalidate-cert"; // adjust according to server settings
    public $emailOut = "";

    // connect to the server and get the inbox emails

    function __construct() {
        $this->connect();
        $this->inbox();
    }

    // close the server connection
    function close() {
        $this->inbox = array();
        $this->msg_cnt = 0;

        imap_close($this->conn);
    }

    // open the server connection
    // the imap_open function parameters will need to be changed for the particular server
    // these are laid out to connect to a Dreamhost IMAP server
    function connect() {
        $this->conn = imap_open("{" . $this->server . ":" . $this->port . "}", $this->user, $this->pass);
    }

    // move the message to a new folder
    function move($msg_index, $folder = 'INBOX.Processed') {
        // move on server
        imap_mail_move($this->conn, $msg_index, $folder);
        imap_expunge($this->conn);

        // re-read the inbox
        $this->inbox();
    }

    // get a specific message (1 = first email, 2 = second email, etc.)
    function get($msg_index = NULL) {
        if (count($this->inbox) <= 0) {
            return array();
        } elseif (!is_null($msg_index) && isset($this->inbox[$msg_index])) {
            $msg_index--;
            return $this->inbox[$msg_index];
        }

        return $this->inbox[0];
    }

    // read the inbox
    function inbox() {
        $this->folders = imap_listmailbox($this->conn, "{" . $this->server . ":" . $this->port . "/novalidate-cert}", "*");

        $this->msg_cnt = imap_num_msg($this->conn);
        $in = array();
        for ($i = 1; $i <= $this->msg_cnt; $i++) {
            $in[] = array(
                'index' => $i,
                'header' => imap_headerinfo($this->conn, $i),
                'body' => imap_fetchbody($this->conn, $i, 1),
                'bodyHTML' => imap_fetchbody($this->conn, $i, 2),
                'structure' => imap_fetchstructure($this->conn, $i)
            );
        }

        $this->inbox = $in;
        for ($i = $this->msg_cnt - 1; $i >= 0; $i--) {
            $this->outputMessages($i);
        }
    }

    function outputMessages($i) {
        $envel = $this->inbox[$i]["header"];
        $this->emailOut .= '<tr id="' . $this->inbox[$i]['index'] . '" class="email-list">'
                . ' <td class="select-mail"><input type=checkbox></td>'
                . ' <td class="from-mail">' . $envel->reply_to[0]->personal . '</td>'
                . '<td class="subject-mail">' . $envel->subject . '</td>'
                . ' <td class="date-mail">' . $envel->date . '</td>'
                . ' </tr>';
    }

}
