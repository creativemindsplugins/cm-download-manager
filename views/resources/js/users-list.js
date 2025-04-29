jQuery(function($) {
	
	$('.cmdm-users-list-panel').each(function() {
		
		var obj = $(this);
		var membersContainer = obj.find('.members');
		var resultsContainer = obj.find('.search-users-results');
		var searchInput = obj.find('input.search');
		
		
		var searchUsersPost = function(sendData, callback) {
			var fieldName = 'cmdm-action';
			sendData[fieldName] = 'search-users';
			sendData.nonce = CMDM_users_list.nonce;
			$.post(CMDM_users_list.url, sendData, callback);
		};
		
		var searchUsers = function() {
			var input = $(this);
			var search = $.trim(input.val());
			if (search) {
				searchUsersPost({user: search}, function(data) {
					data = $.trim(data);
					resultsContainer.show();
					if (data.length) {
						resultsContainer.html(data);
						resultsContainer.find('.btn-user-add').click(userAddHandler);
					} else {
						resultsContainer.html('No results');
					}
				});
			}
		};
		
		
		var addUser = function(userId, userName) {
			if (membersContainer.find('li input[value='+ userId +']').length == 0) {
				var member = membersContainer.find('li:first').clone();
				member.find('span').text(userName);
				member.find('input').val(userId);
				member.find('a').click(removeHandler);
				membersContainer.append(member);
				refreshNoMembers();
			}
		};
		
		var userAddHandler = function() {
			var item = $(this).parents('li');
			var userId = item.data('userId');
			addUser(userId, item.data('userDisplayName'));
			return false;
		}
		
		searchInput.keyup(function() {
			clearTimeout(this.searchTimer);
			this.searchTimer = setTimeout(function() {
				searchUsers.apply(searchInput[0]);
			}, 500);
		});
		
		var removeHandler = function() {
			$(this).parents('li').remove();
			refreshNoMembers();
			return false;
		};
		
		obj.find('.members a').click(removeHandler);
		
		
		obj.find('.show-all-users').click(function() {
			var container = obj.find('.all-users');
			container.hide();
			searchUsersPost({all:true}, function(data) {
				var select = container.find('select');
				select.html('');
				jQuery.each(data, function(i, user) {
					select.append($('<option/>', {value:user[0]}).text(user[1]));
				});
				container.show();
				container.find('a.add').click(function() {
					container.find('select option:selected').each(function() {
						var item = $(this);
						addUser(item.val(), item.text());
					});
					return false;
				});
			});
			return false;
		});
		
		var refreshNoMembers = function() {
			if (obj.find('.members li').length > 1) {
				obj.find('.no-members').hide();
				obj.find('.members').show();
			} else {
				obj.find('.no-members').show();
				obj.find('.members').hide();
			}
		};
		
		refreshNoMembers();
		
	});
	
});