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
 *
 * Plugin Name: EDA Health Slam
 * Version: 1.0
 * Author: Ueli Banholzer <ueli@whatwedo.ch>
 * Author URI: https://whatwedo.ch
 * License: All rights reserved
 */

 /**
 * @author Ueli Banholzer <ueli@whatwedo.ch>
 */
class EdaHealthSlam
{
    protected $postTypes = [];
    protected $taxonomies = [];

    public function __construct()
    {
        require(__DIR__ . "/post-classes/contribution.php");
        require(__DIR__ . "/post-types/contribution.php");
        require(__DIR__ . "/upload.php");
        require(__DIR__ . "/vote.php");
        $this->postTypes["coupons"] = new EDA_HealthSlam_PostType_Contribution();

        add_action('init', array(&$this, 'wpInit'));

        // extend session time to 1 hour
        add_filter('wp_session_expiration', function() { return 60 * 60 * 24; });
    }

    public function wpInit()
    {
        new EdaHealthSlam_Upload();
        new EdaHealthSlam_Vote();
    }

    /**
     * returns contribution posts
     * @param int $limit
     * @param int $page
     * @return EDA_HealthSlam_PostClass_Contribution[]
     */
    public static function getContributions($limit = 20, $page = 1)
    {
        $query = new WP_Query([
            'posts_per_page' => $limit,
            'offset' => ($page - 1),
            'post_type' => 'contribution',
            'post_status' => 'publish',
        ]);

        $posts = [];
        foreach ($query->get_posts() as $post) {
            $posts[] = new EDA_HealthSlam_PostClass_Contribution($post);
        }

        return $posts;
    }

    /**
     * returns a contribution
     * @param int $id
     * @return EDA_HealthSlam_PostClass_Contribution
     */
    public static function getContributionById($id)
    {
        if ((int) $id <= 0) {
            return null;
        }

        $query = new WP_Query([
            'id' => (int) $id,
            'post_type' => 'contribution',
            'post_status' => 'publish',
        ]);

        foreach ($query->get_posts() as $post) {
            return new EDA_HealthSlam_PostClass_Contribution($post);
        }

        return null;
    }

    /**
     * searches a contribution by token
     * @param $token
     * @return EDA_HealthSlam_PostClass_Contribution|null
     */
    public static function getContributionByVoteToken($token)
    {
        foreach (static::getContributions(-1) as $contribution) {
            if ($contribution->hasConfirmationToken($token)) {
                return $contribution;
            }
        }

        return null;
    }

    /**
     * searches a contribution by email
     * @param $token
     * @return EDA_HealthSlam_PostClass_Contribution|null
     */
    public static function getContributionByVoteEmail($email)
    {
        foreach (static::getContributions(-1) as $contribution) {
            if ($contribution->hasConfirmationOfEmail($email)) {
                return $contribution;
            }
        }

        return null;
    }

    public static function activate() {}
    public static function deactivate() {}

}

register_activation_hook(__FILE__, array('EdaHealthSlam', 'activate'));
register_deactivation_hook(__FILE__, array('EdaHealthSlam', 'deactivate'));

$cp_clients = new EdaHealthSlam();

