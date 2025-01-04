<?php

if (!defined('ABSPATH')) {
    exit;
}

class Pustakabilitas_User_Roles {
    
    public function __construct() {
        add_action('init', array($this, 'register_roles'));
        add_action('admin_init', array($this, 'add_role_caps'));
        add_action('admin_menu', array($this, 'add_pending_users_menu'));
        add_action('admin_post_approve_user', array($this, 'approve_user'));
        add_action('admin_post_reject_user', array($this, 'reject_user'));
    }

    /**
     * Register custom roles
     */
    public function register_roles() {
        // Add pending subscriber role
        add_role(
            'pending_subscriber',
            __('Pending Subscriber', 'pustakabilitas'),
            array(
                'read' => true,
                'level_0' => true
            )
        );
        
        // Add approved subscriber role with additional capabilities
        add_role(
            'approved_subscriber',
            __('Approved Subscriber', 'pustakabilitas'),
            array(
                'read' => true,
                'read_private_posts' => true,
                'download_books' => true,
                'read_books' => true,
                'level_0' => true
            )
        );
    }

    /**
     * Add capabilities to roles
     */
    public function add_role_caps() {
        // Add custom capabilities to administrator
        $admin = get_role('administrator');
        $admin->add_cap('approve_users');
        $admin->add_cap('download_books');
        $admin->add_cap('read_books');
    }

    /**
     * Add pending users menu
     */
    public function add_pending_users_menu() {
        add_users_page(
            __('Pending Users', 'pustakabilitas'),
            __('Pending Users', 'pustakabilitas'),
            'approve_users',
            'pending-users',
            array($this, 'render_pending_users_page')
        );
    }

    /**
     * Render pending users page
     */
    public function render_pending_users_page() {
        if (!current_user_can('approve_users')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'pustakabilitas'));
        }
        
        $pending_users = get_users(array('role' => 'pending_subscriber'));
        
        ?>
        <div class="wrap">
            <h1><?php _e('Pending Users', 'pustakabilitas'); ?></h1>
            
            <?php if (empty($pending_users)) : ?>
                <p><?php _e('No pending users found.', 'pustakabilitas'); ?></p>
            <?php else : ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php _e('Username', 'pustakabilitas'); ?></th>
                            <th><?php _e('Email', 'pustakabilitas'); ?></th>
                            <th><?php _e('Registration Date', 'pustakabilitas'); ?></th>
                            <th><?php _e('Actions', 'pustakabilitas'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pending_users as $user) : ?>
                            <tr>
                                <td><?php echo esc_html($user->user_login); ?></td>
                                <td><?php echo esc_html($user->user_email); ?></td>
                                <td><?php echo date_i18n(get_option('date_format'), strtotime($user->user_registered)); ?></td>
                                <td>
                                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                        <?php wp_nonce_field('approve_user_' . $user->ID); ?>
                                        <input type="hidden" name="action" value="approve_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                        <input type="submit" class="button button-primary" value="<?php _e('Approve', 'pustakabilitas'); ?>">
                                    </form>
                                    
                                    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" style="display:inline;">
                                        <?php wp_nonce_field('reject_user_' . $user->ID); ?>
                                        <input type="hidden" name="action" value="reject_user">
                                        <input type="hidden" name="user_id" value="<?php echo $user->ID; ?>">
                                        <input type="submit" class="button" value="<?php _e('Reject', 'pustakabilitas'); ?>">
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Approve user
     */
    public function approve_user() {
        if (!current_user_can('approve_users')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'pustakabilitas'));
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        
        if (!$user_id || !wp_verify_nonce($_POST['_wpnonce'], 'approve_user_' . $user_id)) {
            wp_die(__('Invalid request', 'pustakabilitas'));
        }
        
        $user = new WP_User($user_id);
        
        if ($user->exists()) {
            // Remove pending role and add approved role
            $user->remove_role('pending_subscriber');
            $user->add_role('approved_subscriber');
            
            // Send approval email
            $this->send_approval_email($user);
        }
        
        wp_redirect(admin_url('users.php?page=pending-users'));
        exit;
    }

    /**
     * Reject user
     */
    public function reject_user() {
        if (!current_user_can('approve_users')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'pustakabilitas'));
        }
        
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        
        if (!$user_id || !wp_verify_nonce($_POST['_wpnonce'], 'reject_user_' . $user_id)) {
            wp_die(__('Invalid request', 'pustakabilitas'));
        }
        
        $user = new WP_User($user_id);
        
        if ($user->exists()) {
            // Send rejection email before deleting
            $this->send_rejection_email($user);
            
            // Delete user
            require_once(ABSPATH . 'wp-admin/includes/user.php');
            wp_delete_user($user_id);
        }
        
        wp_redirect(admin_url('users.php?page=pending-users'));
        exit;
    }

    /**
     * Send approval email
     */
    private function send_approval_email($user) {
        $to = $user->user_email;
        $subject = sprintf(__('[%s] Account Approved', 'pustakabilitas'), get_bloginfo('name'));
        
        $message = sprintf(
            __('Hello %s,

Your account has been approved. You can now log in and access all features of the site.

Login URL: %s

Best regards,
%s', 'pustakabilitas'),
            $user->display_name,
            wp_login_url(),
            get_bloginfo('name')
        );
        
        wp_mail($to, $subject, $message);
    }

    /**
     * Send rejection email
     */
    private function send_rejection_email($user) {
        $to = $user->user_email;
        $subject = sprintf(__('[%s] Account Not Approved', 'pustakabilitas'), get_bloginfo('name'));
        
        $message = sprintf(
            __('Hello %s,

We regret to inform you that your account registration has not been approved.

If you believe this is an error, please contact us.

Best regards,
%s', 'pustakabilitas'),
            $user->display_name,
            get_bloginfo('name')
        );
        
        wp_mail($to, $subject, $message);
    }
}

// Initialize roles
new Pustakabilitas_User_Roles();
