<?php

namespace laravel\bridgebb\auth\provider;

if (!defined('IN_PHPBB')) {
    exit;
}

define('LARAVEL_URL', 'http://yourdomain.com/');
define('BRIDGEBB_API_KEY', "secretkey");


// class bridgebb implements phpbb_auth_provider_base
class bridgebb extends \phpbb\auth\provider\base
{
  /**
   * Database Authentication Constructor
   *
   * @param    phpbb_db_driver    $db
   */
  public function __construct(\phpbb\db\driver\driver_interface $db)
  {
    $this->db = $db;
  }

  /**
   * {@inheritdoc}
   */
  // ALL IN ONE
  public function login($username, $password)
  {
    // _login
    if (is_null($password)) {
        return array(
          'status' => LOGIN_ERROR_PASSWORD,
          'error_msg' => 'NO_PASSWORD_SUPPLIED',
          'user_row' => array('user_id' => ANONYMOUS),
        );
    }
    if (is_null($username)) {
        return array(
          'status' => LOGIN_ERROR_USERNAME,
          'error_msg' => 'LOGIN_ERROR_USERNAME',
          'user_row' => array('user_id' => ANONYMOUS),
        );
    }
    // API Validate
    $request = fopen(LARAVEL_URL . 'bridgebb/login/' . BRIDGEBB_API_KEY . '/' . $username . '/' . $password, "r");
    $request = json_decode(stream_get_contents($request), true);
    $oResponse = $request;
    if ($oResponse['response'] === 'success') {
      //TODO: Consume returned user account information like email
      // Get main User type
      // global $db;
      // $sql = 'SELECT id, username, type
      //     FROM ' . "users" . "
      //     WHERE username = '" . $db->sql_escape($username) . "'";
      // $result = $db->sql_query($sql);
      // $row = $db->sql_fetchrow($result);
      // $db->sql_freeresult($result);
      // User group_id: 2 = registered, 4 = global moderators, 5 = administrators
      // if($row) {
      //   $user_group_id = 2;
      //   if($row['type'] == 'superadmin') {
      //     $user_group_id = 5;
      //   }
      //   if($row['type'] == 'admin') {
      //     $user_group_id = 4;
      //   }
      // }
      $user_group_id = 2;

      // Handle auth success
      // Get user by username
      global $db;
      $sql = 'SELECT user_id, username, user_password, user_passchg, user_email, user_type
          FROM ' . USERS_TABLE . "
          WHERE username = '" . $db->sql_escape($username) . "'";
      $result = $db->sql_query($sql);
      $row = $db->sql_fetchrow($result);
      $db->sql_freeresult($result);
      // Does User exist?
      if ($row) {
          // User inactive
          if ($row['user_type'] == USER_INACTIVE || $row['user_type'] == USER_IGNORE) {
              return array(
                'status' => LOGIN_ERROR_ACTIVE,
                'error_msg' => 'ACTIVE_ERROR',
                'user_row' => $row,
              );
          } else {
              $row['user_login_attempts'] = '0';
              return array(
                  'status' => LOGIN_SUCCESS,
                  'error_msg' => false,
                  'user_row' => $row,
              );
          }
      } else {
          // echo "DEBUG: User does not exist. Creating account.<br>";
          // this is the user's first login so create an empty profile

          // Create user row
          global $user;
          // first retrieve default group id
          // Get default group ID
          global $db;
          $sql = 'SELECT group_id
          FROM ' . GROUPS_TABLE . "
          WHERE group_name = '" . $db->sql_escape('REGISTERED') . "'
              AND group_type = " . GROUP_SPECIAL;
          $result = $db->sql_query($sql);
          $row = $db->sql_fetchrow($result);
          $db->sql_freeresult($result);

          if (!$row) {
              trigger_error('NO_GROUP');
          }

          // Check if user is admin

          // generate user account data
          $newUser = array(
              'username' => $username,
              'user_password' => phpbb_hash($password),
              'user_email' => '', //TODO: Set this from the laravel users later
              'group_id' => (int) $user_group_id,
              'user_type' => USER_NORMAL,
              'user_ip' => $user->ip,
          );

          $row['user_login_attempts'] = '0';
          return array(
              'status' => LOGIN_SUCCESS_CREATE_PROFILE,
              'error_msg' => false,
              'user_row' => $newUser,
          );
      }
    } else {
      return array(
            'status' => LOGIN_ERROR_USERNAME,
            'error_msg' => 'LOGIN_ERROR_USERNAME',
            'user_row' => array('user_id' => ANONYMOUS),
      );
    }
  }

}