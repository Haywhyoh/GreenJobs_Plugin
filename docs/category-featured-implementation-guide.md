# Category Featured Slider Implementation Guide

This guide provides practical examples and tips for implementing the Category Featured Slider shortcode on your website.

## Quick Start

The simplest way to add a category featured slider to your page or post is:

```
[black_potential_pipeline_category_featured category="nature-based-work"]
```

## Common Implementation Scenarios

### 1. Industry Highlight Section on Homepage

Add a slider showcasing professionals from each major industry category:

```html
<section class="industry-highlights">
    <h2>Discover Black Professionals by Industry</h2>
    
    <div class="industry-category">
        <h3>Nature-Based Work</h3>
        [black_potential_pipeline_category_featured category="nature-based-work" count="8" items_per_slide="4"]
    </div>
    
    <div class="industry-category">
        <h3>Environmental Policy</h3>
        [black_potential_pipeline_category_featured category="environmental-policy" count="8" items_per_slide="4"]
    </div>
    
    <div class="industry-category">
        <h3>Climate Science</h3>
        [black_potential_pipeline_category_featured category="climate-science" count="8" items_per_slide="4"]
    </div>
    
    <div class="industry-category">
        <h3>Green Construction & Infrastructure</h3>
        [black_potential_pipeline_category_featured category="green-construction" count="8" items_per_slide="4"]
    </div>
</section>
```

### 2. Featured Category on Industry-Specific Pages

For a page dedicated to a specific industry, showcase professionals at the top:

```html
<div class="industry-page-header">
    <h1>Climate Science Professionals</h1>
    <p>Discover talented Black professionals in climate science ready to make an impact.</p>
</div>

<!-- Featured slider at the top -->
[black_potential_pipeline_category_featured 
  category="climate-science" 
  title="Featured Climate Scientists" 
  count="6"]

<!-- Full category directory with search below -->
<div class="full-directory-section">
    <h2>Browse All Climate Science Professionals</h2>
    [black_potential_pipeline_category category="climate-science" layout="grid" per_page="12"]
</div>
```

### 3. Landing Page for Employer Partners

Create a landing page showing professionals across different categories:

```html
<section class="employer-landing">
    <div class="hero-section">
        <h1>Connect with Top Black Talent in Green Industries</h1>
        <p>Browse our curated pipeline of qualified professionals ready to make an impact.</p>
    </div>
    
    <!-- Dynamic tabs for each category -->
    <div class="category-tabs">
        <ul class="nav nav-tabs" id="industryTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="nature-tab" data-toggle="tab" href="#nature" role="tab">Nature-Based Work</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="policy-tab" data-toggle="tab" href="#policy" role="tab">Environmental Policy</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="science-tab" data-toggle="tab" href="#science" role="tab">Climate Science</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="construction-tab" data-toggle="tab" href="#construction" role="tab">Green Construction</a>
            </li>
        </ul>
        
        <div class="tab-content" id="industryTabContent">
            <div class="tab-pane fade show active" id="nature" role="tabpanel">
                [black_potential_pipeline_category_featured category="nature-based-work" title="" count="8"]
            </div>
            <div class="tab-pane fade" id="policy" role="tabpanel">
                [black_potential_pipeline_category_featured category="environmental-policy" title="" count="8"]
            </div>
            <div class="tab-pane fade" id="science" role="tabpanel">
                [black_potential_pipeline_category_featured category="climate-science" title="" count="8"]
            </div>
            <div class="tab-pane fade" id="construction" role="tabpanel">
                [black_potential_pipeline_category_featured category="green-construction" title="" count="8"]
            </div>
        </div>
    </div>
    
    <div class="cta-section">
        <h2>Need more options?</h2>
        <p>Browse our complete directory or contact us for personalized candidate recommendations.</p>
        <a href="/directory" class="btn btn-primary">View Full Directory</a>
        <a href="/contact" class="btn btn-secondary">Contact Us</a>
    </div>
</section>
```

## Customizing the Appearance

### 1. Using Custom CSS with Bootstrap Version

If you're using the Bootstrap version (`use_bootstrap="yes"`), you can add custom CSS for further styling:

```css
/* Customize the slider appearance */
#bppCategorySlider_nature_based_work .carousel-item {
    padding: 20px 0;
}

/* Make the cards more distinctive */
#bppCategorySlider_nature_based_work .card {
    border-radius: 10px;
    border: none;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
}

/* Emphasize the card title */
#bppCategorySlider_nature_based_work .card-title {
    font-weight: 700;
    color: #2c3e50;
}

/* Style the industry badge */
#bppCategorySlider_nature_based_work .badge {
    font-size: 0.7rem;
    padding: 0.4rem 0.6rem;
    background-color: #28a745;
}
```

### 2. Using Custom CSS with Non-Bootstrap Version

If you're using the non-Bootstrap version (`use_bootstrap="no"`), you can override the included styles:

```css
/* Override slider container styles */
.bpp-featured-container {
    max-width: 100%;
    padding: 3rem 2rem;
    background-color: #f9f9f9;
}

/* Customize the card appearance */
.bpp-professional-card {
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s, box-shadow 0.3s;
}

/* Emphasize professional name */
.bpp-professional-name {
    font-size: 1.4rem;
    color: #333;
}

/* Style the slider controls */
.bpp-slider-prev,
.bpp-slider-next {
    background: #333;
    width: 50px;
    height: 50px;
}
```

## Best Practices

1. **Performance Optimization**
   - Limit the `count` parameter to a reasonable number (8-12 is recommended)
   - Consider using optimized images for professional photos
   - If adding multiple sliders to one page, keep total count low

2. **Mobile Experience**
   - Test the slider on various mobile devices
   - The slider automatically adjusts for mobile, but verify it works well with your theme

3. **Accessibility**
   - The shortcode includes basic accessibility features
   - Consider adding additional context for screen readers if needed

4. **Integration with Page Builders**
   - Works well with most WordPress page builders
   - For Elementor, Beaver Builder, etc., use their Text/HTML widgets to add the shortcode

5. **Browser Compatibility**
   - Tested and works on modern browsers (Chrome, Firefox, Safari, Edge)
   - Provides fallback styling for older browsers

## Troubleshooting

**Issue**: Slider doesn't display any professionals
**Solution**: 
- Verify the category slug is correct
- Check that there are approved professionals in that category
- Check browser console for JavaScript errors

**Issue**: Slider appears but carousel controls don't work
**Solution**:
- If using Bootstrap version, ensure Bootstrap JS is properly loaded
- Check for JavaScript conflicts with your theme

**Issue**: Styling doesn't match site design
**Solution**:
- Use custom CSS to override slider styles
- For deeper customization, copy the template file to your theme and modify it

## Example: Complete Page Template

Here's an example of how to create a complete page template that features the category slider:

```php
<?php
/**
 * Template Name: Industry Showcase
 */

get_header();
?>

<div class="industry-showcase-container">
    <div class="page-header">
        <h1><?php the_title(); ?></h1>
        <?php the_content(); ?>
    </div>
    
    <div class="featured-categories">
        <?php
        // Get all industries 
        $industries = get_terms(array(
            'taxonomy' => 'bpp_industry',
            'hide_empty' => true,
        ));
        
        // Display each industry in a featured slider
        if (!empty($industries) && !is_wp_error($industries)) {
            foreach ($industries as $industry) {
                echo '<div class="industry-section">';
                echo '<h2>' . esc_html($industry->name) . '</h2>';
                echo do_shortcode('[black_potential_pipeline_category_featured category="' . esc_attr($industry->slug) . '" count="8" items_per_slide="4"]');
                echo '<p class="view-all"><a href="' . esc_url(get_term_link($industry)) . '">View all ' . esc_html($industry->name) . ' professionals</a></p>';
                echo '</div>';
            }
        }
        ?>
    </div>
</div>

<?php get_footer(); ?> 