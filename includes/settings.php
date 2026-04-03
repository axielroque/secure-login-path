<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'lcloak_add_settings_page' );
add_action( 'admin_init', 'lcloak_register_settings' );

function lcloak_add_settings_page() {
    add_options_page(
        __( 'Login Cloak', 'axiel-secure-login-path' ),
        __( 'Login Cloak', 'axiel-secure-login-path' ),
        'manage_options',
        'login-cloak',
        'lcloak_render_settings_page'
    );
}

function lcloak_sanitize_block_behavior( $value ) {
    $value = (string) $value;
    $allowed = [ 'redirect', '404', '403' ];
    if ( ! in_array( $value, $allowed, true ) ) {
        $value = 'redirect';
    }
    return $value;
}

function lcloak_register_settings() {
    if ( ! get_option( 'lcloak_login_slug' ) ) {
        $legacy_slug = get_option( 'slp_login_slug' );
        if ( $legacy_slug ) {
            update_option( 'lcloak_login_slug', $legacy_slug );
        }
    }

    if ( ! get_option( 'lcloak_block_behavior' ) ) {
        $legacy_behavior = get_option( 'slp_block_behavior' );
        if ( $legacy_behavior ) {
            update_option( 'lcloak_block_behavior', $legacy_behavior );
        }
    }

    register_setting(
        'lcloak_settings',
        'lcloak_login_slug',
        [
            'type'              => 'string',
            'sanitize_callback' => 'lcloak_sanitize_login_slug',
        ]
    );

    register_setting(
        'lcloak_settings',
        'lcloak_block_behavior',
        [
            'type'              => 'string',
            'sanitize_callback' => 'lcloak_sanitize_block_behavior',
            'default'           => 'redirect',
        ]
    );
}

function lcloak_sanitize_login_slug( $value ) {
    $value = sanitize_title( (string) $value );
    $forbidden = (array) apply_filters( 'lcloak_forbidden_login_slugs', [ 'login', 'wp-login', 'admin', 'wp-admin', 'wp-login.php' ] );
    $min_length = (int) apply_filters( 'lcloak_min_login_slug_length', 6 );
    if ( $value === '' || in_array( $value, $forbidden, true ) || strlen( $value ) < $min_length ) {
        add_settings_error( 'lcloak_settings', 'lcloak_invalid_slug', __( 'Invalid login path. Choose a longer, non-obvious value.', 'axiel-secure-login-path' ), 'error' );
        return (string) get_option( 'lcloak_login_slug' );
    }
    return $value;
}

// Automatically flush rewrite rules when the slug changes
add_action( 'update_option_lcloak_login_slug', 'lcloak_flush_on_slug_change', 10, 2 );
add_action( 'update_option_slp_login_slug', 'lcloak_flush_on_slug_change', 10, 2 );
function lcloak_flush_on_slug_change( $old_value, $new_value ) {
    if ( $old_value !== $new_value ) {
        // Register the rule BEFORE flushing
        lcloak_register_rewrite_rule();

        // Flush on shutdown so WordPress can recognize the rule
        add_action( 'shutdown', function() {
            flush_rewrite_rules();
        } );
    }
}

function lcloak_render_settings_page() {
    // Generate a random slug if the button was pressed
    if ( isset( $_POST['lcloak_generate'] ) ) {
        $nonce = isset( $_POST['lcloak_generate_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['lcloak_generate_nonce'] ) ) : '';
        if ( current_user_can( 'manage_options' ) && $nonce && wp_verify_nonce( $nonce, 'lcloak_generate_action' ) ) {
            $new_slug = lcloak_generate_random_slug();
            update_option( 'lcloak_login_slug', $new_slug );
            update_option( 'slp_login_slug', $new_slug );
        }
        // No flush needed here because update_option triggers our hook
    } elseif ( isset( $_POST['slp_generate'] ) ) {
        // Legacy submit support
        $nonce = isset( $_POST['slp_generate_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['slp_generate_nonce'] ) ) : '';
        if ( current_user_can( 'manage_options' ) && $nonce && wp_verify_nonce( $nonce, 'slp_generate_action' ) ) {
            $new_slug = lcloak_generate_random_slug();
            update_option( 'lcloak_login_slug', $new_slug );
            update_option( 'slp_login_slug', $new_slug );
        }
        // No flush needed here because update_option triggers our hook
    }

    $slug = lcloak_get_login_slug();
    $behavior = get_option( 'lcloak_block_behavior', 'redirect' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__( 'Login Cloak', 'axiel-secure-login-path' ); ?></h1>
        <?php settings_errors( 'lcloak_settings' ); ?>

        <p>
            <strong><?php echo esc_html__( 'Login URL:', 'axiel-secure-login-path' ); ?></strong><br>
            <code><?php echo esc_url( home_url( '/' . $slug . '/' ) ); ?></code>
            <button type="button" class="button lcloak-copy" data-url="<?php echo esc_attr( home_url( '/' . $slug . '/' ) ); ?>" data-success="<?php echo esc_attr__( 'Copied!', 'axiel-secure-login-path' ); ?>" data-error="<?php echo esc_attr__( 'Copy failed', 'axiel-secure-login-path' ); ?>"><?php echo esc_html__( 'Copy', 'axiel-secure-login-path' ); ?></button>
        </p>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'lcloak_settings' );
            ?>
            <input type="text" name="lcloak_login_slug" value="<?php echo esc_attr( $slug ); ?>" />

            <p>
                <strong><?php echo esc_html__( 'Block behavior:', 'axiel-secure-login-path' ); ?></strong><br>
                <select name="lcloak_block_behavior">
                    <option value="redirect" <?php selected( $behavior, 'redirect' ); ?>><?php echo esc_html__( 'Redirect to home (302)', 'axiel-secure-login-path' ); ?></option>
                    <option value="404" <?php selected( $behavior, '404' ); ?>><?php echo esc_html__( '404 Not Found', 'axiel-secure-login-path' ); ?></option>
                    <option value="403" <?php selected( $behavior, '403' ); ?>><?php echo esc_html__( '403 Forbidden', 'axiel-secure-login-path' ); ?></option>
                </select>
            </p>
            <?php submit_button(); ?>
        </form>

        <form method="post">
            <input type="hidden" name="lcloak_generate" value="1">
            <?php wp_nonce_field( 'lcloak_generate_action', 'lcloak_generate_nonce' ); ?>
            <?php submit_button( __( 'Generate Random Path', 'axiel-secure-login-path' ), 'secondary' ); ?>
        </form>

        <hr>

        <p>
            <strong><?php echo esc_html__( 'Recovery mode:', 'axiel-secure-login-path' ); ?></strong><br>
            <code><?php echo esc_url( wp_login_url() . '?lcloak-recover=1' ); ?></code>
            <button type="button" class="button lcloak-copy" data-url="<?php echo esc_attr( wp_login_url() . '?lcloak-recover=1' ); ?>" data-success="<?php echo esc_attr__( 'Copied!', 'axiel-secure-login-path' ); ?>" data-error="<?php echo esc_attr__( 'Copy failed', 'axiel-secure-login-path' ); ?>"><?php echo esc_html__( 'Copy', 'axiel-secure-login-path' ); ?></button>
        </p>

        <p style="color:#666;font-size:13px;">
            <?php echo esc_html__( 'Developed by', 'axiel-secure-login-path' ); ?> <strong>Axiel Roque</strong> ·
            <a href="https://github.com/axielroque" target="_blank"><?php echo esc_html__( 'GitHub', 'axiel-secure-login-path' ); ?></a>
        </p>
    </div>
    <script>
    (function(){
      function copyText(text){
        if (navigator.clipboard && navigator.clipboard.writeText) {
          return navigator.clipboard.writeText(text);
        }
        return new Promise(function(resolve, reject){
          try {
            var ta=document.createElement('textarea');
            ta.value=text;
            ta.setAttribute('readonly','');
            ta.style.position='absolute';
            ta.style.left='-9999px';
            document.body.appendChild(ta);
            ta.select();
            var ok=document.execCommand && document.execCommand('copy');
            document.body.removeChild(ta);
            ok ? resolve() : reject();
          } catch(e){ reject(e); }
        });
      }
      document.addEventListener('click', function(e){
        var btn = e.target && e.target.closest ? e.target.closest('button.lcloak-copy') : (e.target && e.target.classList && e.target.classList.contains('lcloak-copy') ? e.target : null);
        if (!btn) return;
        var url = btn.getAttribute('data-url');
        if (!url) return;
        var original = btn.textContent;
        var successLabel = btn.getAttribute('data-success') || 'Copied!';
        var errorLabel = btn.getAttribute('data-error') || 'Copy failed';
        btn.disabled = true;
        copyText(url).then(function(){
          btn.textContent = successLabel;
          setTimeout(function(){ btn.textContent = original; btn.disabled = false; }, 1200);
        }).catch(function(){
          btn.textContent = errorLabel;
          setTimeout(function(){ btn.textContent = original; btn.disabled = false; }, 1500);
        });
      });
    })();
    </script>
    <?php
}
