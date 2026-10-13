<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package TH_THEME
 */

get_header();
?>

<div id="404" class="site-main sec-wrap">
	<div class="text-center pt80 pb80">
		<p class="en fw-bold fs48 sm-fs32 mb40 sm-mb40 text-uppercase">404 <br class="pcnone">File not found</p>
		<h1 class="fw-bold fs32 sm-fs20 mb40 sm-mb40">指定されたページは存在しません。</h1>
		<p class="fs14 sm-fs14 mb80 sm-mb80">
			大変申し訳ございませんが、お探しのページは、<br class="pcnone">削除されたか、<br>ファイル名が間違っている可能性があります。 ア<br class="pcnone">ドレスを確認してください。
		</p>
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="btn btn-back"><span class="arw"></span>BACK TO
			TOP</a>
	</div>
</div>

<?php
get_footer();
