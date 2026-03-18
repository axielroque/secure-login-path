<?php
defined( 'ABSPATH' ) || exit;

add_action( 'admin_menu', 'slp_add_settings_page' );
add_action( 'admin_init', 'slp_register_settings' );

function slp_add_settings_page() {
    add_options_page(
        __( 'Secure Login Path', 'secure-login-path' ),
        __( 'Secure Login Path', 'secure-login-path' ),
        'manage_options',
        'secure-login-path',
        'slp_render_settings_page'
    );
}

function slp_sanitize_block_behavior( $value ) {
    $value = (string) $value;
    $allowed = [ 'redirect', '404', '403' ];
    if ( ! in_array( $value, $allowed, true ) ) {
        $value = 'redirect';
    }
    return $value;
}

function slp_register_settings() {
    register_setting(
        'slp_settings',
        'slp_login_slug',
        [
            'type'              => 'string',
            'sanitize_callback' => 'slp_sanitize_login_slug',
        ]
    );

    register_setting(
        'slp_settings',
        'slp_block_behavior',
        [
            'type'              => 'string',
            'sanitize_callback' => 'slp_sanitize_block_behavior',
            'default'           => 'redirect',
        ]
    );
}

function slp_sanitize_login_slug( $value ) {
    $value = sanitize_title( (string) $value );
    $forbidden = apply_filters( 'slp_forbidden_login_slugs', [ 'login', 'wp-login', 'admin', 'wp-admin', 'wp-login.php' ] );
    $min_length = (int) apply_filters( 'slp_min_login_slug_length', 6 );
    if ( $value === '' || in_array( $value, $forbidden, true ) || strlen( $value ) < $min_length ) {
        add_settings_error( 'slp_settings', 'slp_invalid_slug', __( 'Invalid login path. Choose a longer, non-obvious value.', 'secure-login-path' ), 'error' );
        return (string) get_option( 'slp_login_slug' );
    }
    return $value;
}

// Automatically flush rewrite rules when the slug changes
add_action( 'update_option_slp_login_slug', 'slp_flush_on_slug_change', 10, 2 );
function slp_flush_on_slug_change( $old_value, $new_value ) {
    if ( $old_value !== $new_value ) {
        // Register the rule BEFORE flushing
        slp_register_rewrite_rule();

        // Flush on shutdown so WordPress can recognize the rule
        add_action( 'shutdown', function() {
            flush_rewrite_rules();
        } );
    }
}

function slp_render_settings_page() {
    // Generate a random slug if the button was pressed
    if ( isset( $_POST['slp_generate'] ) ) {
        $nonce = isset( $_POST['slp_generate_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['slp_generate_nonce'] ) ) : '';
        if ( current_user_can( 'manage_options' ) && $nonce && wp_verify_nonce( $nonce, 'slp_generate_action' ) ) {
            update_option( 'slp_login_slug', slp_generate_random_slug() );
        }
        // No flush needed here because update_option triggers our hook
    }

    $slug = slp_get_login_slug();
    $behavior = get_option( 'slp_block_behavior', 'redirect' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html__( 'Secure Login Path', 'secure-login-path' ); ?></h1>
        <?php settings_errors( 'slp_settings' ); ?>

        <p>
            <strong><?php echo esc_html__( 'Login URL:', 'secure-login-path' ); ?></strong><br>
            <code><?php echo esc_url( home_url( '/' . $slug . '/' ) ); ?></code>
            <button type="button" class="button slp-copy" data-url="<?php echo esc_attr( home_url( '/' . $slug . '/' ) ); ?>" data-success="<?php echo esc_attr__( 'Copied!', 'secure-login-path' ); ?>" data-error="<?php echo esc_attr__( 'Copy failed', 'secure-login-path' ); ?>"><?php echo esc_html__( 'Copy', 'secure-login-path' ); ?></button>
        </p>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'slp_settings' );
            ?>
            <input type="text" name="slp_login_slug" value="<?php echo esc_attr( $slug ); ?>" />

            <p>
                <strong><?php echo esc_html__( 'Block behavior:', 'secure-login-path' ); ?></strong><br>
                <select name="slp_block_behavior">
                    <option value="redirect" <?php selected( $behavior, 'redirect' ); ?>><?php echo esc_html__( 'Redirect to home (302)', 'secure-login-path' ); ?></option>
                    <option value="404" <?php selected( $behavior, '404' ); ?>><?php echo esc_html__( '404 Not Found', 'secure-login-path' ); ?></option>
                    <option value="403" <?php selected( $behavior, '403' ); ?>><?php echo esc_html__( '403 Forbidden', 'secure-login-path' ); ?></option>
                </select>
            </p>
            <?php submit_button(); ?>
        </form>

        <form method="post">
            <input type="hidden" name="slp_generate" value="1">
            <?php wp_nonce_field( 'slp_generate_action', 'slp_generate_nonce' ); ?>
            <?php submit_button( __( 'Generate Random Path', 'secure-login-path' ), 'secondary' ); ?>
        </form>

        <hr>

        <p>
            <strong><?php echo esc_html__( 'Recovery mode:', 'secure-login-path' ); ?></strong><br>
            <code><?php echo esc_url( wp_login_url() . '?slp-recover=1' ); ?></code>
            <button type="button" class="button slp-copy" data-url="<?php echo esc_attr( wp_login_url() . '?slp-recover=1' ); ?>" data-success="<?php echo esc_attr__( 'Copied!', 'secure-login-path' ); ?>" data-error="<?php echo esc_attr__( 'Copy failed', 'secure-login-path' ); ?>"><?php echo esc_html__( 'Copy', 'secure-login-path' ); ?></button>
        </p>

        <p style="color:#666;font-size:13px;">
            <?php echo esc_html__( 'Developed by', 'secure-login-path' ); ?> <strong>Axiel Roque</strong> ·
            <a href="https://github.com/axielroque" target="_blank"><?php echo esc_html__( 'GitHub', 'secure-login-path' ); ?></a>
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
        var btn = e.target && e.target.closest ? e.target.closest('button.slp-copy') : (e.target && e.target.classList && e.target.classList.contains('slp-copy') ? e.target : null);
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
