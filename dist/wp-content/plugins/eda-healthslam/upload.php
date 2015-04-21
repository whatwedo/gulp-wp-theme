<?php
/*
 * Copyright (c) 2015, whatwedo GmbH
 * All rights reserved
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 * 
 * 1. Redistributions of source code must retain the above copyright notice, 
 *    this list of conditions and the following disclaimer.
 * 
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation 
 *    and/or other materials provided with the distribution.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT 
 * NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR 
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, 
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 */
 /**
 * @author Ueli Banholzer <ueli@whatwedo.ch>
 */
class EdaHealthSlam_Upload
{
    const SESSION_UPLOAD_KEY = 'eda_upload';
    const POST_META_UPLOAD_KEY = '_eda_upload_key';
    const POST_META_UPLOAD_IP_KEY = '_eda_uploaded_by_ip';

    protected $session;
    protected static $validationErrors = [];

    public function __construct()
    {
        $this->session = WP_Session::get_instance();

        $this->checkUpload();
    }

    /**
     * check if the contribution form is submitted or not
     * @return bool
     */
    public static function isSubmitted()
    {
        if (!isset($_POST['key'])
            || !static::isValidKey($_POST['key'])) {
            return false;
        }

        // validiere Formular
        $errors = [];

        foreach([
            'title',
            'short_description',
            'contact_name',
            'contact_phone',
            'contact_city',
            ] as $notEmpty) {
            if (!isset($_POST[$notEmpty])
                || strlen($_POST[$notEmpty]) <= 3) {
                $errors[] = $notEmpty;
            }
        }

        if (!isset($_POST['contact_email'])
            || !filter_var($_POST['contact_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'contact_email';
        }

        if (count($errors) > 0) {
            static::$validationErrors = $errors;
            return true;
        }

        // create contribution
        $post = wp_insert_post([
            'post_title' => $_POST['title'],
            'post_type' => 'contribution',
            'post_status' => 'draft',
        ]);

        // save custom fields
        update_field('field_553253fa12c5a', $_POST['short_description'], $post);
        update_field('field_553253868b317', $_POST['contact_name'], $post);
        update_field('field_553253a28b318', $_POST['contact_email'], $post);
        update_field('field_553253aa8b319', $_POST['contact_phone'], $post);
        update_field('field_553253b68b31a', $_POST['contact_city'], $post);
        update_field('field_5532541112c5b', $_POST['comment'], $post);

        $files = get_field('field_553253588b315', $post);

        if (!is_array($files)) {
            $files = [];
        }

        foreach (static::getUploadedFiles($_POST['key']) as $attachment) {
            $files[] = ['field_5532536b8b316' => $attachment->ID];
        }

        update_field('field_553253588b315', $files, $post);

        // clear upload keys so that it's not possible to resubmit the page
        $session = WP_Session::get_instance();
        $session[static::SESSION_UPLOAD_KEY] = [];

        return true;
    }

    /**
     * return form validation errors
     * @return array
     */
    public static function getErrors()
    {
        return static::$validationErrors;
    }

    /**
     * returns a field value based on submitted form
     * @param $field
     * @return string
     */
    public static function getFormValue($field)
    {
        if (!isset($_POST[$field])) {
            return '';
        }

        return htmlspecialchars(stripslashes($_POST[$field]));
    }

    /**
     * Returns all uploaded files to a key
     * @param $key
     * @return array
     */
    public static function getUploadedFiles($key)
    {
        if (!static::isValidKey($key)) {
            return [];
        }

        $query = new WP_Query([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'meta_key'  => static::POST_META_UPLOAD_KEY,
            'meta_value'  => $key,
        ]);

        return $query->get_posts();
    }

    /**
     * uploads data
     */
    public function checkUpload()
    {
        if (isset($_GET['type'])
            && isset($_GET['key'])
            && $this->isValidKey($_GET['key'])) {

            if (!($file = $this->doUpload($_GET['key'])) instanceof WP_Error) {
                die(json_encode([
                    'success' => true,
                ]));
            }

            header('HTTP/1.0 400 Bad Request');
            header('X-Error-Message: ' . $file->get_error_message());
            _e('Es ist ein Fehler beim Hochladen der Datei aufgetreten.', 'healthslam');
            exit;
        }
    }

    /**
     * stores the file in the wordpress attachment database
     * @param $key
     * @return bool|int|WP_Error
     */
    public function doUpload($key)
    {
        if (!function_exists('media_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
        }

        $upload = media_handle_upload('file', 0);

        if ($upload instanceof WP_Error) {
            return $upload;
        }

        add_post_meta($upload, static::POST_META_UPLOAD_KEY, $key, true)
            || update_post_meta($upload, static::POST_META_UPLOAD_KEY, $key);

        add_post_meta($upload, static::POST_META_UPLOAD_IP_KEY, $_SERVER['REMOTE_ADDR'], true)
            || update_post_meta($upload, static::POST_META_UPLOAD_IP_KEY, $_SERVER['REMOTE_ADDR']);

        return true;
    }

    /**
     * returns true if a unique key is given for an user
     * @param $key
     * @return bool
     */
    public static function isValidKey($key)
    {
        $session = WP_Session::get_instance();
        if (preg_match('/([a-z0-9]){12}-([a-z0-9]){12}/', $key)
            && array_search($key, $session[static::SESSION_UPLOAD_KEY]->toArray()) !== false) {
            return true;
        }

        return false;
    }

    /**
     * creates a static unique ID to upload the files
     * @return string
     */
    public static function generateUploadKey()
    {
        $session = WP_Session::get_instance();
        if (!is_array($session[static::SESSION_UPLOAD_KEY])) {
            $session[static::SESSION_UPLOAD_KEY] = [];
        }

        $key = implode('-',str_split(substr(md5(mt_rand()),0,24), 12));

        $session[static::SESSION_UPLOAD_KEY][] = $key;

        return $key;
    }


}
