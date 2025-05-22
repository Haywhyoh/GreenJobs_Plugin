# Category Featured Slider Shortcode

## Overview

The Category Featured Slider shortcode (`[black_potential_pipeline_category_featured]`) provides a responsive, carousel-based display of professionals from a specific industry category. Unlike the standard category directory, this shortcode is designed for showcasing professionals without search filters, making it ideal for promotional sections or landing pages.

## Features

- **Category-Specific Display**: Shows only professionals from the specified industry category
- **Responsive Carousel/Slider**: Adapts to different screen sizes
- **Configurable Items Per Slide**: Control how many professionals appear per slide on large screens
- **Mobile-Friendly**: Automatically adjusts to show fewer items on smaller screens
- **Touch-Enabled**: Supports swipe gestures on mobile devices
- **Bootstrap Integration**: Optional Bootstrap styling for easy theme integration
- **Clickable Cards**: Each professional card links directly to their full profile
- **Visual Feedback**: Includes hover effects for better user interaction

## Usage

### Basic Usage

```
[black_potential_pipeline_category_featured category="nature-based-work"]
```

This example displays professionals from the "nature-based-work" category in a slider format.

### Full Example with All Options

```
[black_potential_pipeline_category_featured 
  category="nature-based-work" 
  title="Nature-Based Work Professionals" 
  count="8" 
  items_per_slide="4" 
  use_bootstrap="yes"]
```

## Parameters

| Parameter | Description | Default | Required |
|-----------|-------------|---------|----------|
| `category` | Slug of the industry category to display | None | Yes |
| `title` | Custom title for the slider section | Auto-generated based on category | No |
| `count` | Maximum number of professionals to display | 12 | No |
| `items_per_slide` | Number of professionals to show per slide on large screens | 4 | No |
| `use_bootstrap` | Whether to use Bootstrap styling (yes/no) | yes | No |

## Card Layout

Each professional card in the slider displays:

1. **Image**: Full-width profile photo at the top (or placeholder icon if no image exists)
2. **Name**: Professional's name
3. **Title**: Professional's job title
4. **Experience**: Years of experience
5. **Industry**: Category/industry label

All cards are clickable and link directly to the professional's full profile.

## Responsive Behavior

The slider automatically adapts to different screen sizes:
- **Large screens** (≥992px): Shows the number of items specified in `items_per_slide` (default: 4)
- **Medium screens** (≥768px and <992px): Shows 2 items per slide
- **Small screens** (<768px): Shows 1 item per slide

## Technical Implementation

### Files Involved

- `public/partials/bpp-category-featured.php`: The main template file that renders the slider
- `public/class-bpp-public.php`: Contains the `display_category_featured()` method
- `includes/class-bpp-shortcodes.php`: Contains the `render_category_featured()` method
- `includes/class-bpp.php`: Registers the shortcode

### Key Technical Features

1. **Category Matching**: Uses both taxonomy terms and meta fields to find professionals in the specified category
2. **Combined Query**: Implements a custom SQL query to handle both taxonomy and meta field data
3. **Responsive Design**: Uses CSS media queries and JavaScript to adapt the layout for different screen sizes
4. **Touch Support**: Implements touch event handlers for mobile swipe gestures
5. **Accessibility**: Includes proper ARIA attributes and keyboard navigation support

## Examples

### Example 1: Basic Category Display

```
[black_potential_pipeline_category_featured category="environmental-policy"]
```

### Example 2: Custom Title and Count

```
[black_potential_pipeline_category_featured 
  category="climate-science" 
  title="Featured Climate Scientists" 
  count="6"]
```

### Example 3: Non-Bootstrap Version with More Items Per Slide

```
[black_potential_pipeline_category_featured 
  category="green-construction" 
  items_per_slide="5" 
  use_bootstrap="no"]
```

## Notes

- The slider automatically handles cases where there aren't enough professionals to fill a slide
- The shortcode will display a friendly message if no professionals are found in the specified category
- Bootstrap 5.3.0 is used for the Bootstrap-styled version
- Custom CSS is provided for the non-Bootstrap version 