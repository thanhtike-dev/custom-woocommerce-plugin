<?php
function th_pagination( $custom_query ) {

	$total_pages     = $custom_query->max_num_pages;
	$current_page    = max( 1, get_query_var( 'paged' ) );
	$pagination_base = get_pagenum_link( 1 );
	$format          = '?paged=%#%';

	if ( $total_pages > 1 ) {
		echo '<div class="posts-pagination fs14 fw-semi-bold pt40 d-flex items-center justify-center col-gap-20">';

		echo paginate_links(
			array(
				'base'      => $pagination_base . '%_%',
				'format'    => $format,
				'current'   => $current_page,
				'total'     => $total_pages,
				'prev_text' => '<svg class="pagi-circle-svg" width="24" height="24" viewBox="0 0 24 24"   fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="pagi-circle" d="M12 24C18.6274 24 24 18.6274 24 12C24 5.37258 18.6274 0 12 0C5.37258 0 0 5.37258 0 12C0 18.6274 5.37258 24 12 24Z"/>
                            <path class="pagi-chev" d="M14 16.9998L9 11.9998L14 6.99984" stroke="white" stroke-width="1.5"/>
                            </svg>',
				'next_text' => '<svg class="pagi-circle-svg" width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path class="pagi-circle" d="M12 24C18.6274 24 24 18.6274 24 12C24 5.37258 18.6274 0 12 0C5.37258 0 0 5.37258 0 12C0 18.6274 5.37258 24 12 24Z"/>
                            <path class="pagi-chev" d="M10 7.00016L15 12.0002L10 17.0002" stroke="white" stroke-width="1.5"/>
                            </svg>',

			)
		);

		echo '</div>';
	}
}
