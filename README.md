# Black Potential Pipeline WordPress Plugin

A WordPress plugin to create a curated database of Black professionals seeking green jobs. The plugin provides a submission form for candidates, an admin screening interface, and a public-facing directory organized by industry categories.

## Description

The Black Potential Pipeline plugin helps organizations build a public-facing talent pipeline that employers can tap into when looking for Black candidates in green industries. The plugin features:

- Custom submission form for Black professionals to submit their information
- Admin dashboard for screening and approving applicants
- Public directory of approved professionals organized by industry categories
- Shortcodes for easy integration into any WordPress site
- Responsive design for both desktop and mobile devices
- Customizable settings and appearance

## Installation

1. Upload the `black-potential-pipeline` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin settings through the 'Black Potential' menu in the admin dashboard

## Usage

### Shortcodes

The plugin provides several shortcodes for displaying different elements:

1. **Submission Form**
   ```
   [black_potential_pipeline_form]
   ```
   Displays the application submission form for candidates.

   Optional attributes:
   - `title` - Custom title for the form
   - `success_message` - Custom message displayed after successful submission

2. **Full Directory**
   ```
   [black_potential_pipeline_directory]
   ```
   Displays the complete directory of approved professionals.

   Optional attributes:
   - `title` - Custom title for the directory
   - `per_page` - Number of professionals to display per page (default: 12)
   - `layout` - Display layout, either 'grid' or 'list' (default: 'grid')

3. **Category Directory**
   ```
   [black_potential_pipeline_category category="nature-based-work"]
   ```
   Displays professionals from a specific industry category.

   Required attributes:
   - `category` - The slug of the industry category to display

   Optional attributes:
   - `title` - Custom title for the directory
   - `per_page` - Number of professionals to display per page (default: 12)
   - `layout` - Display layout, either 'grid' or 'list' (default: 'grid')

4. **Featured Professionals**
   ```
   [black_potential_pipeline_featured]
   ```
   Displays a selection of featured professionals.

   Optional attributes:
   - `title` - Custom title for the section
   - `count` - Number of featured professionals to display (default: 4)
   - `layout` - Display layout, either 'carousel', 'grid', or 'list' (default: 'carousel')

5. **Statistics Display**
   ```
   [black_potential_pipeline_stats]
   ```
   Displays statistics about the pipeline.

   Optional attributes:
   - `title` - Custom title for the section
   - `show_categories` - Whether to show category breakdown (yes/no, default: yes)
   - `show_total` - Whether to show total counts (yes/no, default: yes)

### Admin Interface

The plugin adds a new menu item called "Black Potential" to the WordPress admin dashboard with the following pages:

1. **Dashboard** - Overview of application statistics and quick actions
2. **New Applications** - Review and process new submissions
3. **Approved Professionals** - Manage approved professionals
4. **Rejected Applications** - View rejected applications
5. **Settings** - Configure plugin options

## Industry Categories

The plugin comes with four predefined industry categories:

1. Nature-based work
2. Environmental policy
3. Climate science
4. Green construction & infrastructure

## Settings

The plugin settings are organized into several sections:

1. **Email Notifications** - Configure email settings for notifications
2. **Form Fields** - Customize the submission form fields
3. **Directory Display** - Configure how the directory appears on the front end
4. **Approval Workflow** - Set up the approval process

## Developer Information

### Hooks and Filters

The plugin provides several hooks and filters for developers to extend its functionality:

1. **Filters:**
   - `bpp_form_fields` - Modify the form fields
   - `bpp_directory_query_args` - Modify the query arguments for the directory
   - `bpp_professional_data` - Modify professional data before display

2. **Actions:**
   - `bpp_before_submission_process` - Runs before processing a submission
   - `bpp_after_submission_process` - Runs after processing a submission
   - `bpp_before_approval` - Runs before approving an applicant
   - `bpp_after_approval` - Runs after approving an applicant
   - `bpp_before_rejection` - Runs before rejecting an applicant
   - `bpp_after_rejection` - Runs after rejecting an applicant

### Custom Post Type and Taxonomy

The plugin registers a custom post type `bpp_applicant` and a custom taxonomy `bpp_industry`.

## Requirements

- WordPress 5.7 or higher
- PHP 7.4 or higher
- MySQL 5.6 or higher

## License

This plugin is licensed under the GPL v2 or later.

## Support

For support, please contact [your support email or website].

## Credits

Developed by [Your Name or Organization]. 