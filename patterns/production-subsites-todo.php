<?php
/**
 * Production Subsites: Todo
 * Handler for 3rd-party Block
 *
 * @package figuren-theater/theater-production-subsites
 */

return array(
	'title'         => __( 'Prepare & Setup', 'theater-production-subsites' ),
	'viewportWidth' => 500,
	'categories'    => array( 'page', 'theatrebase' ),
	'postTypes'     => Figuren_Theater\Production_Subsites\Registration\get_supported_post_types(),
	'blockTypes'    => array( 'core/post-content' ),
	'content'       => '<!-- wp:heading -->
<h2>' . __( 'Prepare & Setup', 'theater-production-subsites' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:pluginette/todo-block-list {"className":"is-style-ft-todo"} -->
<div class="wp-block-pluginette-todo-block-list is-style-ft-todo"><!-- wp:pluginette/todo-block-item -->
<div class="wp-block-pluginette-todo-block-item">Akkus sind aufgeladen</div>
<!-- /wp:pluginette/todo-block-item -->

<!-- wp:pluginette/todo-block-item -->
<div class="wp-block-pluginette-todo-block-item">Waschlappen ist eingepackt</div>
<!-- /wp:pluginette/todo-block-item -->

<!-- wp:pluginette/todo-block-item -->
<div class="wp-block-pluginette-todo-block-item">Kostüm ist gewaschen und eingepackt</div>
<!-- /wp:pluginette/todo-block-item -->

<!-- wp:pluginette/todo-block-item -->
<div class="wp-block-pluginette-todo-block-item">Wasser ist auch eingepackt</div>
<!-- /wp:pluginette/todo-block-item -->

<!-- wp:pluginette/todo-block-item -->
<div class="wp-block-pluginette-todo-block-item">Flyer &amp; Plakate dieser und weiterer Inszenierungen habe ich dabei</div>
<!-- /wp:pluginette/todo-block-item --></div>
<!-- /wp:pluginette/todo-block-list -->

<!-- wp:paragraph -->
<p>Braucht ggf. das Auto auch noch etwas Liebe bevor es los geht?</p>
<!-- /wp:paragraph -->

<!-- wp:pluginette/todo-block-list {"className":"is-style-ft-todo"} -->
<div class="wp-block-pluginette-todo-block-list is-style-ft-todo"><!-- wp:pluginette/todo-block-item -->
<div class="wp-block-pluginette-todo-block-item">Ölstand überprüft</div>
<!-- /wp:pluginette/todo-block-item -->

<!-- wp:pluginette/todo-block-item -->
<div class="wp-block-pluginette-todo-block-item">Scheiben-Wasch-Wasser aufgefüllt</div>
<!-- /wp:pluginette/todo-block-item -->

<!-- wp:pluginette/todo-block-item -->
<div class="wp-block-pluginette-todo-block-item">Tank voll</div>
<!-- /wp:pluginette/todo-block-item --></div>
<!-- /wp:pluginette/todo-block-list -->',
);
