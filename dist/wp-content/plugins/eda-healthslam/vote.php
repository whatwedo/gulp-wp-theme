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
class EdaHealthSlam_Vote
{
    protected static $alreadyConfirmed = false;

    /**
     * returns entered email address
     * @return string
     */
    public static function getFormValue()
    {
        if (!isset($_POST['email'])) {
            return '';
        }

        return htmlspecialchars(stripslashes($_POST['email']));
    }

    /**
     * @return null|EDA_HealthSlam_PostClass_Contribution
     */
    public static function getSelectedContribution()
    {
        if (isset($_GET['confirm'])) {
            $contribution = EdaHealthSlam::getContributionByVoteToken($_GET['confirm']);
            if ($contribution instanceof EDA_HealthSlam_PostClass_Contribution) {
                return $contribution;
            }
        }

        if (!isset($_GET['id'])) {
            return null;
        }

        return EdaHealthSlam::getContributionById($_GET['id']);
    }

    /**
     * @return bool
     */
    public static function isSubmitted()
    {
        return isset($_POST['email']);
    }

    /**
     * @return bool
     */
    public static function isSuccess()
    {
        $contribution = static::getSelectedContribution();

        if ($contribution === null) {
            return false;
        }

        if (isset($_POST['email'])
            && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)
            && !static::hasUserVoted($_POST['email'])) {
            static::sendEmail(
                $_POST['email'],
                $contribution,
                static::generateLink($contribution, $_POST['email'])
            );

            return true;
        }
        return false;
    }

    /**
     * sends a notification email to the user
     * @param string                                $email
     * @param EDA_HealthSlam_PostClass_Contribution $contribution
     * @param string                                $link
     */
    public static function sendEmail($email, EDA_HealthSlam_PostClass_Contribution $contribution, $link)
    {
        wp_mail($email,
                static::getConfirmationEmailText('email_confirmation_subject', $contribution, $link),
                static::getConfirmationEmailText('email_confirmation_body', $contribution, $link)
            );
    }

    /**
     * replaces placeholders in email text
     *
     * @param                                       $field
     * @param EDA_HealthSlam_PostClass_Contribution $contribution
     * @param                                       $link
     * @return mixed
     */
    protected static function getConfirmationEmailText($field, EDA_HealthSlam_PostClass_Contribution $contribution, $link)
    {
        return str_replace([
            '{BEITRAGNAME}',
            '{LINK}',
        ], [
            $contribution->getTitle(),
            $link,
        ],
        get_field($field, 'option'));
    }

    /**
     * generates a validation link
     * @param EDA_HealthSlam_PostClass_Contribution $contribution
     * @return string
     */
    public static function generateLink(EDA_HealthSlam_PostClass_Contribution $contribution, $email)
    {
        $token = $contribution->createConfirmationToken($email, $_SERVER['REMOTE_ADDR']);

        return get_the_permalink() . '?confirm=' . $token;
    }

    /**
     * checks if the user has already voted
     * @param $email
     * @return bool
     */
    private static function hasUserVoted($email)
    {
        if (EdaHealthSlam::getContributionByVoteEmail($email) instanceof EDA_HealthSlam_PostClass_Contribution) {
            return true;
        }

        return false;
    }

    /**
     * returns true if the current given address already voted
     * @return bool
     */
    public static function hasVoted()
    {
        return static::hasUserVoted($_POST['email']);
    }

    /**
     * @return bool
     */
    public static function isConfirming()
    {
        if (!isset($_GET['confirm'])
            || $_GET['confirm'] == '') {
            return false;
        }

        $contribution = EdaHealthSlam::getContributionByVoteToken($_GET['confirm']);

        if (!$contribution instanceof EDA_HealthSlam_PostClass_Contribution) {
            return false;
        }

        if ($contribution->isTokenConfirmed($_GET['confirm'])) {
            static::$alreadyConfirmed = true;
        } else {
            $contribution->setTokenConfirmed($_GET['confirm']);
        }
        return true;
    }

    /**
     * @return bool
     */
    public static function isAlreadyConfirmed()
    {
        return static::$alreadyConfirmed;
    }
}
