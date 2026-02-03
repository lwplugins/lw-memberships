/**
 * LW Memberships - Plan Editor JS
 *
 * Handles AJAX search for content and member tabs.
 */
( function() {
	'use strict';

	/**
	 * Initialize content search on the Content tab.
	 */
	function initContentSearch() {
		var input = document.getElementById( 'lw-mship-content-search' );
		var results = document.getElementById( 'lw-mship-content-results' );
		var list = document.getElementById( 'lw-mship-content-list' );

		if ( ! input || ! results || ! list ) {
			return;
		}

		var timer = null;

		input.addEventListener( 'input', function() {
			clearTimeout( timer );
			var val = input.value.trim();

			if ( val.length < 2 ) {
				clearElement( results );
				return;
			}

			timer = setTimeout( function() {
				searchPosts( val, results, list );
			}, 300 );
		} );
	}

	/**
	 * Search posts via AJAX.
	 */
	function searchPosts( search, resultsEl, listEl ) {
		var url = lwMshipPlanEditor.ajaxUrl +
			'?action=lw_mship_search_posts&nonce=' + lwMshipPlanEditor.nonce +
			'&search=' + encodeURIComponent( search );

		fetch( url )
			.then( function( response ) {
				return response.json();
			} )
			.then( function( data ) {
				if ( ! data.success ) {
					return;
				}

				clearElement( resultsEl );
				var existing = getExistingPostIds( listEl );

				data.data.forEach( function( post ) {
					if ( existing.indexOf( post.id ) !== -1 ) {
						return;
					}

					var div = document.createElement( 'div' );
					div.className = 'lw-mship-search-result';

					var span = document.createElement( 'span' );
					span.textContent = post.title;
					var small = document.createElement( 'small' );
					small.textContent = ' (' + post.post_type + ')';
					span.appendChild( small );

					var btn = document.createElement( 'button' );
					btn.type = 'button';
					btn.className = 'button button-small';
					btn.textContent = lwMshipPlanEditor.i18n.add;

					btn.addEventListener( 'click', function() {
						addContentItem( listEl, post );
						div.remove();
					} );

					div.appendChild( span );
					div.appendChild( btn );
					resultsEl.appendChild( div );
				} );
			} );
	}

	/**
	 * Add a content item to the list.
	 */
	function addContentItem( listEl, post ) {
		var row = document.createElement( 'div' );
		row.className = 'lw-mship-content-item';

		var hidden = document.createElement( 'input' );
		hidden.type = 'hidden';
		hidden.name = 'content_post_ids[]';
		hidden.value = post.id;

		var span = document.createElement( 'span' );
		span.textContent = post.title;
		var small = document.createElement( 'small' );
		small.textContent = ' (' + post.post_type + ')';
		span.appendChild( small );

		var btn = document.createElement( 'button' );
		btn.type = 'button';
		btn.className = 'button button-small button-link-delete';
		btn.textContent = lwMshipPlanEditor.i18n.remove;

		btn.addEventListener( 'click', function() {
			row.remove();
		} );

		row.appendChild( hidden );
		row.appendChild( span );
		row.appendChild( btn );
		listEl.appendChild( row );
	}

	/**
	 * Get existing post IDs from the content list.
	 */
	function getExistingPostIds( listEl ) {
		var inputs = listEl.querySelectorAll( 'input[name="content_post_ids[]"]' );
		var ids = [];
		inputs.forEach( function( input ) {
			ids.push( parseInt( input.value, 10 ) );
		} );
		return ids;
	}

	/**
	 * Initialize member search on the Members tab.
	 */
	function initMemberSearch() {
		var input = document.getElementById( 'lw-mship-member-search' );
		var results = document.getElementById( 'lw-mship-member-results' );
		var hidden = document.getElementById( 'add_member_user_id' );

		if ( ! input || ! results || ! hidden ) {
			return;
		}

		var timer = null;

		input.addEventListener( 'input', function() {
			clearTimeout( timer );
			var val = input.value.trim();

			if ( val.length < 2 ) {
				clearElement( results );
				return;
			}

			timer = setTimeout( function() {
				searchUsers( val, results, hidden, input );
			}, 300 );
		} );
	}

	/**
	 * Search users via AJAX.
	 */
	function searchUsers( search, resultsEl, hiddenEl, inputEl ) {
		var url = lwMshipPlanEditor.ajaxUrl +
			'?action=lw_mship_search_users&nonce=' + lwMshipPlanEditor.nonce +
			'&search=' + encodeURIComponent( search );

		fetch( url )
			.then( function( response ) {
				return response.json();
			} )
			.then( function( data ) {
				if ( ! data.success ) {
					return;
				}

				clearElement( resultsEl );

				data.data.forEach( function( user ) {
					var div = document.createElement( 'div' );
					div.className = 'lw-mship-search-result';
					div.style.cursor = 'pointer';

					var span = document.createElement( 'span' );
					span.textContent = user.name + ' (' + user.email + ')';
					div.appendChild( span );

					div.addEventListener( 'click', function() {
						hiddenEl.value = user.id;
						inputEl.value = user.name + ' (' + user.email + ')';
						clearElement( resultsEl );
					} );

					resultsEl.appendChild( div );
				} );
			} );
	}

	/**
	 * Initialize remove buttons for existing content items.
	 */
	function initRemoveButtons() {
		document.querySelectorAll( '.lw-mship-content-item .button-link-delete' ).forEach( function( btn ) {
			btn.addEventListener( 'click', function() {
				btn.closest( '.lw-mship-content-item' ).remove();
			} );
		} );
	}

	/**
	 * Remove all child nodes from an element.
	 */
	function clearElement( el ) {
		while ( el.firstChild ) {
			el.removeChild( el.firstChild );
		}
	}

	// Initialize when DOM is ready.
	document.addEventListener( 'DOMContentLoaded', function() {
		initContentSearch();
		initMemberSearch();
		initRemoveButtons();
	} );
} )();
