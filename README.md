# WP Serverless Search
A static search plugin for WordPress. Search through thousands of posts in milliseconds, with support for fuzzy matching and support for custom themes.

[View Demo](https://agitated-brahmagupta5490.on.getshifter.io/) on [Shifter](https://www.getshifter.io)

## Installation

1. Download WP Serverless Search WordPress Plugin as a Zip Archive
2. Upload to your WordPress site or blog
3. Activate

## Customizing to your Theme

The default settings for this WordPress Plugin are designed to work with the official Twenty Seventeen WordPress Theme. However, you can customize these optiosn for custom Themes for site search forms.

1. Navigate to the WP Serverless Search WordPress Plugin Settings
2. Update Form Class to best match your theme search form.
3. Update Input Class to best match the input of the search form.

### Finding Your Form and Input Class

The best way to do this is using your browser developer tools such as Inspect Element in Chrome, Developer Tools in Safari, etc.

For example, here is the site search form HTML from the twenty seventeen WordPress Theme.

In this example, our target Form Class could be `form[role=search]`, `form.search-form`, or simply `.search-form`. You decide how specific you want to get, based on your Theme and needs.

For the Input Class, that could be any of the following: `input[type=search]`, `#search-form`, `input.search-field`, etc.

Those values may change depending on your theme.

```html
<form role="search" method="get" class="search-form" action="https://example.com/">
	<label for="search-form">
		<span class="screen-reader-text">Search for:</span>
	</label>
	<input
    type="search" id="search-form" class="search-field" placeholder="Search …" value="" name="s">
	<button type="submit" class="search-submit">
    <span class="screen-reader-text">Search</span>
  </button>
</form>
```

## FAQs

> Q: Is WP Serverless Search a WordPress site search plugin?
> A: Yes!

> Q: Why did you build this?
> A: Few reasons:
> 1. native WordPress Search is slow and inaccurate.
> 2. We needed a site search solution for static WordPres sites using Shifter, so we built one.

> Q: Is it free to use?
> A: Yah, go have fun.

> Q: Is it fast?
> Yes, very fast.

> Q: Why so fast?
> A: It uses simple JavaScript to search a static XML. No database queries or server resources are necessary while searching.

> Q: My site is huge, is that an issue?
> A: Should be okay. We've tested WP Serverless Search up to ~1K posts and was able to return search results within milliseconds.

> Q: Does it support Fuzzy search? e.g. Hallo, Halo, Hello, Helloooo
> A: Yes! It uses Fusejs to provide fuzzy search results for typos.

> Q: Does it support Auto Complete?
> A: Yes, it will search and sort as you type.