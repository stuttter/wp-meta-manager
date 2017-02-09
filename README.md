# WP Meta Manager

WP Meta Manager provides a robust meta management toolset for all metadata in WordPress.

# Installation

* Download and install using the built in WordPress plugin installer.
* Activate in the "Plugins" area of your admin by clicking the "Activate" link.
* No further setup or configuration is necessary.

# FAQ

### Where can I get support?

https://wordpress.org/support/plugin/wp-meta-manager/

### How can I register a custom meta table?

```
/**
 * Register custom meta.
 *
 * @since 1.0
 */
function my_prefix_wp_meta_add_custom_meta() {

	if( ! function_exists( 'my_plugin_function' ) ) {
		return;
	}

	// Register meta table
	wp_register_meta_type( 'customer', array( 
		'table_name' => 'my_customermeta', // Table name without the $wpdb prefix
	) );

}
add_action( 'wp_register_meta_types', 'my_prefix_wp_meta_add_custom_meta' );
```

### Can I contribute?

Yes, please!