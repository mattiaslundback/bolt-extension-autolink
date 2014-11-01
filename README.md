Bolt Autolink
=======================

Autolink extension for the [Bolt CMS](http://www.bolt.cm). Finds  webpages relevant to an entry or page and inserts links to these pages. The extension uses Google Custom Search which makes it possible to restrict the search to certain websites, i e newspapers or blogs in a certain country.

This extension is based on the RSSAggregator code by Sebastian Klier & Gawain Lynch and adapted by Mattias Lundbäck.

Instructions
=======================

1. Download the extension and place it into your app/extensions folder as app/extensions/Autolink

2. Activate it in your app/config/config.yml by adding Autolink to the `enabled_extensions` option.  
Example: `enabled_extensions: [ Autolink, your_other_extensions ... ]`

3. Place the `{{ autolink() }}` Twig function in your template. Two arguments are necessary:

a) The search string for e g Google Custom Search API. It looks like this.

"https://www.googleapis.com/customsearch/v1?key=[INSERT_YOUR_API_KEY]&cx=[Custom_search_engine_ID]&q=%search%&alt=atom"

Instead of the search query insert [%search%] as in the string above. 

How to get the API and ID is decribed by Google at this address: https://developers.google.com/custom-search/json-api/v1/using_rest

For how to create the Custom Search Engine look at this page: https://developers.google.com/custom-search/docs/overview. You need a Google account.

b) The other argument is the twig expression for the search argument, e g the title of the post (record.title). Spaces will automatically be converted to"+" to make it a search string. You can also use other twig functions like "entry.title" as long as it is an one-dimensional string.

The expression will then look like this: {{ autolink('https://www.googleapis.com/customsearch/v1?key=[INSERT_YOUR_API_KEY]&cx=[Custom_search_engine_ID]&q=%search%&alt=atom', record.title) }}

4 You can pass other options to the Twig function:  
`{{ autolink('[search string]', record.title, { 'limit': limit, 'showDesc': true }) }}`  
	+ limit: The amount of links to be shown, default: 10
	+ showDesc: Show the full description, default: false
	+ showDate: Show the date, default: false  
	+ descCutoff: Number of characters to display in the description, default: 100

Customization
=======================

Override the CSS styles defined in Autolink/assets/autolink.css in your own stylesheet.

Support
=======================

No support, you are on your own.

Please use the issue tracker: [Github](http://github.com/sekl/bolt-autolink/issues)
