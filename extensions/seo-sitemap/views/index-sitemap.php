<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * @var array $sitemaps
 */

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<?xml-stylesheet type="text/xsl" href="' . fw_ext('seo-sitemap')->index_xsl_url() . '" ?>';
?>
<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<?php foreach( $sitemaps as $sitemap ) : ?>
		<sitemap>
			<loc><?php echo $sitemap['url']; ?></loc>
		</sitemap>
	<?php endforeach ?>
</sitemapindex>