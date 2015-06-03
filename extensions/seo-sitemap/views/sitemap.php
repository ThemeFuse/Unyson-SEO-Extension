<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/**
 * Sitemap XML file view
 *
 * @var array $sitemaps
 */

echo '<?xml version="1.0" encoding="UTF-8"?>';
echo '<?xml-stylesheet type="text/xsl" href="' . fw_ext('seo-sitemap')->xsl_url() . '" ?>';
?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
	<?php foreach ( $sitemaps as $sitemap ) : ?>
		<url>
			<?php if ( isset( $sitemap['url'] ) ) : ?>
				<loc><?php echo $sitemap['url'] ?></loc>
			<?php endif ?>
			<?php if ( isset( $sitemap['priority'] ) ) : ?>
				<priority><?php echo $sitemap['priority'] ?></priority>
			<?php endif ?>
			<?php if ( isset( $sitemap['frequency'] ) ) : ?>
				<changefreq><?php echo $sitemap['frequency'] ?></changefreq>
			<?php endif ?>
			<?php if ( isset( $sitemap['modified'] ) ) : ?>
				<lastmod><?php echo $sitemap['modified'] ?></lastmod>
			<?php endif ?>
		</url>
	<?php endforeach ?>
</urlset>