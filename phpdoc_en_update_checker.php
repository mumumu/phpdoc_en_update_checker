<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once "vendor/autoload.php";

class MailEntry {
    public $title;
    public $patch;
    public $body;
    public $author;
}

$config = array(
    'server' => 'localhost:25',
    'To' => 'foo@example.com',
    'From' => 'noreply@example.com',
);

function send_email(MailEntry $mailentry) {
    global $config;

    $mailer = new PHPMailer(true);
    $tmpfile = tempnam("/tmp", "phpen_doc_checker");
    file_put_contents($tmpfile, $mailentry->patch);
    try {
        $mailer->IsSMTP();
        $mailer->Host = $config['server'];
        $mailer->addAddress($config['To']);
        $mailer->setFrom($config['From'], $mailentry->author);
        $mailer->addAttachment($tmpfile, 'patch.txt');
        $mailer->Subject = "[DOC-CVS] " . trim($mailentry->title);
        $mailer->Body = $mailentry->body;
        $mailer->send();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mailer->ErrorInfo}\n";
    }
    unlink($tmpfile);
}

function process_feed(object $entries, DateTime $filter_last_updated) {
    foreach($entries as $entry) {
        $entry_updated = new DateTime($entry->updated);
        if ($entry_updated > $filter_last_updated) {
            //echo "Update!!:" . $entry_updated->format("c") . " -> " . $filter_last_updated->format("c") . "\n";
            $author = "";
            $author_name = (string)$entry->author->name;
            $author_email = (string)$entry->author->email;
            if (!empty($author_name)) {
                $author = $author_name;
            } else {
                if (!empty($author_email)) {
                    $author = $author_email;
                }
            }
            $mailentry = new MailEntry();
            $mailentry->title = (string)$entry->title;
            $patch = file_get_contents(
                (string)$entry->link["href"] . ".patch"
            );
            $mailentry->patch = $patch;
            $mailentry->body = join("\n", array_slice(explode("\n", $patch), 5));
            $mailentry->author = $author;

            send_email($mailentry);
        }
    }
}

$feed = file_get_contents("https://github.com/php/doc-en/commits/master.atom");
$cache_path = "/tmp/.phpdoc_en_feed_cache";
$feedxml = new SimpleXMLElement($feed);
$last_updated = $feedxml->updated;
$feed_last_updated = new DateTime($last_updated);

if (file_exists($cache_path)) {
    $cached_last_updated = new DateTime(file_get_contents($cache_path));
    if ($feed_last_updated > $cached_last_updated) {
        process_feed($feedxml->entry, $cached_last_updated);
    } else {
        //echo "lastest feed. exiting...\n";
        exit(1);
    }
} else {
    $dummy_old_last_updated = new DateTime("1970-01-01T00:00:00Z");
    process_feed($feedxml->entry, $dummy_old_last_updated);
}
file_put_contents($cache_path, $last_updated);
