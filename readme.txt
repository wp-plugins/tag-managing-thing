=== Tag Managing Thing ===
Contributors: christined
Tags: tags, admin, categories
Requires at least: 2.3
Tested up to: 2.3b3
Stable tag: trunk

Tag Managing Thing provides a central interface for managing your tags (and categories!)

== Description ==

Tag Managing Thing adds an administration screen for manipulating tags (and categories! (and any other post-based taxonomy that you might have!)).  It allows changing the name and slug of a term,  deleting a term,  splitting a term into multiple terms, merging multiple terms into a single term and changing the taxonomy that a term belongs to.

== Usage Notes ==

*Splitting*
When you split a term,  the things tagged with the selected term will be tagged with each of the split-terms.  By default, the term being split will be deleted;  however, if it is included in the list of split terms,  then it will be retained.

*Merging*
With merging,  the selected terms will be merged into the term that is currently being edited.  Any post which is associated with a merge term will become associated with the term that is being edited.

*Switching*
When switching,  if the term already exists,  then the posts associated with the term in the current taxonomy will become associated with the term in the alternate taxonomy.  If it doesn't exist,  a new term will be created.
In both cases, the term will be removed from the current taxonomy.

== Installation ==
1. Drop the plugin file into your wordpress plugins folder.
1. Enable the plugin.