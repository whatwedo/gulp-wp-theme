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
class EDA_HealthSlam_PostClass_Contribution
{
    /**
     * @var WP_Post
     */
    protected $post = null;

    public function __construct(WP_Post $post)
    {
        if ($post->post_type !== 'contribution') {
            throw new \Exception('invalid post type for EDA_HealthSlam_PostClass_Contribution');
        }

        $this->post = $post;
    }

    public function getId()
    {
        return $this->getPost()->ID;
    }

    public function reload()
    {
        $this->post = get_post($this->getId());
    }

    public function getSlug()
    {
        return $this->post->post_name;
    }

    public function getTitle()
    {
        return $this->post->post_title;
    }

    public function getLink()
    {
        return get_permalink($this->getId());
    }

    public function getVoteLink()
    {
        return get_permalink(84) . '?id=' . $this->getId();
    }

    public function getVideo()
    {
        return $this->getField('video');
    }

    public function getShortDescription()
    {
        return $this->getField('description_short');
    }

    public function getContactName()
    {
        return $this->getField('contact_name');
    }

    public function getField($selector)
    {
        return get_field($selector, $this->getId());
    }

    /**
     * returns confirmation UUID token
     *
     * @param $email
     * @param $ip
     * @return string
     */
    public function createConfirmationToken($email, $ip)
    {
        $key = $this->generateKey();


        $votes = $this->getField('field_553623be64e18');

        if (!is_array($votes)) {
            $votes = [];
        }

        $votes[] = [
            'field_5536331c64e19' => $key,
            'field_5536332b64e1a' => $email,
            'field_5536333864e1b' => $ip,
            'field_5536333f64e1c' => false,
        ];

        update_field('field_553623be64e18', $votes, $this->getId());

        return $key;
    }

    /**
     * @param $token
     * @return bool
     */
    public function hasConfirmationToken($token)
    {
        $votes = $this->getField('votes');

        if (!is_array($votes)) {
            return false;
        }

        foreach ($votes as $vote) {
            if ($vote['uuid'] == $token) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $token
     * @return bool
     */
    public function isTokenConfirmed($token)
    {
        $votes = $this->getField('votes');

        if (!is_array($votes)) {
            return false;
        }

        foreach ($votes as $vote) {
            if ($vote['uuid'] == $token) {
                return (bool) $vote['confirmed'];
            }
        }

        return false;
    }

    /**
     * @param $token
     * @return bool
     */
    public function setTokenConfirmed($token)
    {
        $votes = $this->getField('votes');

        if (!is_array($votes)) {
            return false;
        }

        foreach ($votes as $key => $vote) {
            if ($vote['uuid'] == $token) {
                $votes[$key]['confirmed'] = 1;
            }
        }

        update_field('field_553623be64e18', $votes, $this->getId());

        return false;
    }

    /**
     * @param $token
     * @return bool
     */
    public function hasConfirmationOfEmail($token)
    {
        $votes = $this->getField('votes');

        if (!is_array($votes)) {
            return false;
        }

        foreach ($votes as $vote) {
            if ($vote['email'] == $token) {
                return true;
            }
        }

        return false;
    }

    /**
     * Gibt das WP_Post Objekt zurück
     * @return null|WP_Post
     */
    public function getPost()
    {
        return $this->post;
    }

    /**
     * serialized post object
     * @return string
     */
    public function serialize()
    {
        $post = [
            'title' => $this->getTitle(),
            'short_description' => $this->getShortDescription(),
            'contact_name' => $this->getContactName(),
            'link' => $this->getLink(),
            'video' => [
                'url' => [
                    $this->getVideo()['mime_type'] => $this->getVideo()['url'],
                ],
            ],
        ];

        return $post;
    }

    /*
     * Magic Methods um alle Calls auf das eigentliche WP_Post Objekt ebenfalls anzubieten,
     * z.B. funktioniert $post->ID ebenfalls, wenn $post ein EDA_HealthSlam_PostClass_Contribution-Objekt ist
     */
    public function __get($name)
    {
        return $this->post->$name;
    }

    public function __set($name, $value)
    {
        $this->post->$name = $value;
    }

    public function __call($name, $arguments)
    {
        return call_user_func([$this->post, $name], $arguments);
    }

    /**
     * creates a unique ID to upload the files
     * @return string
     */
    protected static function generateKey()
    {
        $key = implode('-',str_split(substr(md5(mt_rand()),0,24), 12)) . '-' . implode('-',str_split(substr(md5(mt_rand()),0,24), 12));

        return $key;
    }
}
