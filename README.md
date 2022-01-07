Fork from: https://github.com/pyronaur/easy-photography-portfolio

Only difference is the support of footer pagination. In the wp-content/plugins/photography-portfolio/Photography_Portfolio/Core/Query.php file.

To integrate footer to your theme, add this snipped to your footer.php in the theme (e.g. wp-content/themes/twentyseventeen/footer.php)

<?php echo paginate_links([ 'mid_size'=> 3,'end_size'=>1]); ?>
