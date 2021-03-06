<?php

/**
 * Adds and renders the Hummingbird Shortcode
 *
 * @link       https://github.com/simonmcwhinnie/hummingbird
 * @since      0.0.2
 *
 * @package    hummingbird
 * @subpackage hummingbird/includes
 */

class Hummingbird_Shortcode {

	/**
	 * @param $atts
	 * @param string $content
	 */
	public function render_shortcode( $atts, $content = ""){
		wp_enqueue_script('bootstrap-modal');
		wp_enqueue_style('bootstrap-modal');
		$transient_id = 'hummingbird_shortcode_library';
		if($atts){
			foreach($atts as $k => $v){
				$transient_id .= $k.$v;
			}
		}
		$options_name = 'hummingbird_option_name';
		$anime_format = '<div class="anime"><div class = "overlay fade"><h5 class="overlay-anime-title">%s</h5><p class="noselect">Status: %s</p><p class="noselect">Episodes watched: %d</p><p class="noselect">Episode Count: %d</p><button data-toggle="modal" data-target="#%s" class="btn">Details</button></div><img class="anime-cover" src="%s"><div class = "anime-title-wrap"><h3 class = "anime-title">%s</h3></div></div>';
		$lightbox_format = '<div class="modal fade" id="%s"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h3 class="modal-title">%s</h3></div><div class="modal-body"><img class="anime-cover" src="%s"><div class="modal-subcontent"><h4>Synopsis</h4><p>%s</p><h4>Genres</h4><p>%s</p></div></div><div class="modal-footer"><button type="button" class="close btn" data-dismiss="modal" aria-label="Close">Close</button></div></div></div></div>';
		$feed = get_transient( $transient_id );
		if(!$feed){
			$request = new Requests();
			$options = get_option( $options_name );
			$feed = $request->make_request( 'library' , $transient_id, $options['hb_cache_timer'] * 60, $options['hb_username'], $atts );
		}
		if(!$feed){
			return;
		}
		$json_feed = json_decode( $feed['json'] );
		$rendered_shortcode = '<div class = "hummingbird_shortcode">';
		foreach($json_feed as $library_entry){
			$anime = $library_entry->anime;
			$rendered_shortcode .= sprintf( $anime_format, $anime->title, $anime->status, $library_entry->episodes_watched, $anime->episode_count, $anime->slug, $anime->cover_image, $anime->title );
			$genre_list = "";
			foreach($anime->genres as $genre){
				$genre_list .= $genre->name . ", ";
			}
			$genre_list = rtrim( $genre_list, ", " );
			$rendered_shortcode .= sprintf( $lightbox_format, $anime->slug, $anime->title, $anime->cover_image, $anime->synopsis, $genre_list );
		}
		$rendered_shortcode .= '</div>';
		return $rendered_shortcode;
	}
}

add_shortcode( 'hummingbird', array( 'Hummingbird_Shortcode', 'render_shortcode' ) );