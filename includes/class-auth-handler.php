<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Pustakabilitas_Auth_Handler {

    public function __construct() {
        add_action('init', [$this, 'register_custom_fields']);
        add_action('register_form', [$this, 'add_registration_fields']);
        add_action('user_register', [$this, 'save_custom_fields']);
        add_action('show_user_profile', [$this, 'show_custom_fields']);
        add_action('edit_user_profile', [$this, 'show_custom_fields']);
        add_filter('registration_errors', [$this, 'validate_custom_fields'], 10, 3);
    }

    public function register_custom_fields() {
        // Register custom user meta fields
        $fields = [
            'first_name', 'last_name', 'whatsapp', 'gender', 
            'disability_type', 'birth_date', 'city', 'province', 
            'address', 'bio'
        ];

        foreach ($fields as $field) {
            register_meta('user', $field, [
                'type' => 'string',
                'single' => true,
                'show_in_rest' => true,
            ]);
        }
    }

    public function add_registration_fields() {
        ?>
        <p>
            <label for="first_name"><?php _e('First Name', 'pustakabilitas'); ?></label>
            <input type="text" name="first_name" id="first_name" class="input" required />
        </p>
        <p>
            <label for="last_name"><?php _e('Last Name', 'pustakabilitas'); ?></label>
            <input type="text" name="last_name" id="last_name" class="input" required />
        </p>
        <p>
            <label for="whatsapp"><?php _e('WhatsApp Number', 'pustakabilitas'); ?></label>
            <input type="tel" name="whatsapp" id="whatsapp" class="input" required />
        </p>
        <p>
            <label><?php _e('Gender', 'pustakabilitas'); ?></label><br>
            <input type="radio" name="gender" value="male" required /> <?php _e('Male', 'pustakabilitas'); ?>
            <input type="radio" name="gender" value="female" /> <?php _e('Female', 'pustakabilitas'); ?>
        </p>
        <p>
            <label><?php _e('Disability Type', 'pustakabilitas'); ?></label><br>
            <input type="radio" name="disability_type" value="blind" required /> <?php _e('Blind', 'pustakabilitas'); ?>
            <input type="radio" name="disability_type" value="low_vision" /> <?php _e('Low Vision', 'pustakabilitas'); ?>
            <input type="radio" name="disability_type" value="reading_disability" /> <?php _e('Reading Disability', 'pustakabilitas'); ?>
        </p>
        <p>
            <label for="birth_date"><?php _e('Birth Date', 'pustakabilitas'); ?></label>
            <input type="date" name="birth_date" id="birth_date" class="input" required 
                   aria-label="<?php _e('Birth Date (DD/MM/YYYY)', 'pustakabilitas'); ?>" />
        </p>
        <p>
            <label for="city"><?php _e('City', 'pustakabilitas'); ?></label>
            <input type="text" name="city" id="city" class="input" required />
        </p>
        <p>
            <label for="province"><?php _e('Province', 'pustakabilitas'); ?></label>
            <input type="text" name="province" id="province" class="input" required />
        </p>
        <p>
            <label for="address"><?php _e('Address', 'pustakabilitas'); ?></label>
            <textarea name="address" id="address" class="input" required></textarea>
        </p>
        <p>
            <label for="bio"><?php _e('Short Bio', 'pustakabilitas'); ?></label>
            <textarea name="bio" id="bio" class="input"></textarea>
        </p>
        <p>
            <input type="checkbox" name="terms_agreement" id="terms_agreement" required />
            <label for="terms_agreement"><?php _e('I agree to the Terms and Conditions', 'pustakabilitas'); ?></label>
        </p>
        <div class="terms-content" style="max-height: 200px; overflow-y: auto; padding: 10px; border: 1px solid #ddd;">
            <?php echo $this->get_terms_content(); ?>
        </div>
        <?php
    }

    private function get_terms_content() {
        return '<h4>' . __('Terms and Conditions', 'pustakabilitas') . '</h4>' .
               '<p>' . __('By registering as a member, you agree to the following statements:', 'pustakabilitas') . '</p>' .
               '<ol>' .
               '<li>' . __('I declare that I am a person with visual impairment/reading limitations...', 'pustakabilitas') . '</li>' .
               '<li>' . __('I agree not to duplicate, publish, sell or transfer books...', 'pustakabilitas') . '</li>' .
               '<li>' . __('I am ready to accept the risks if I violate the provisions...', 'pustakabilitas') . '</li>' .
               '</ol>';
    }

    public function save_custom_fields($user_id) {
        if (!empty($_POST['first_name'])) {
            update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
        }
        // ... save other fields similarly ...

        // Set default role as subscriber
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
    }

    public function validate_custom_fields($errors, $sanitized_user_login, $user_email) {
        if (empty($_POST['first_name'])) {
            $errors->add('first_name_error', __('First name is required.', 'pustakabilitas'));
        }
        // ... validate other required fields ...

        if (!isset($_POST['terms_agreement'])) {
            $errors->add('terms_error', __('You must agree to the terms and conditions.', 'pustakabilitas'));
        }

        return $errors;
    }
}

new Pustakabilitas_Auth_Handler();
