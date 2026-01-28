/**
 * NTH Notifications - Admin JavaScript
 * 
 * @package NTH\Notifications
 */

(function() {
	'use strict';

	// Wait for DOM to be ready
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}

	/**
	 * Initialize all event listeners
	 */
	function init() {
		// Toggle token visibility
		initTokenToggle();

		// Add Chat ID buttons
		initAddChatId();

		// Remove Chat ID buttons
		initRemoveChatId();

		// Test Telegram buttons
		initTestTelegram();

		// Test Zalo buttons
		initTestZalo();

		// Find Zalo Chat ID button
		initFindZaloChatId();
	}

	/**
	 * Toggle Bot Token visibility
	 */
	function initTokenToggle() {
		const toggleButtons = document.querySelectorAll('.nth-notify__toggle-token');
		
		toggleButtons.forEach(function(button) {
			button.addEventListener('click', function(e) {
				e.preventDefault();
				
				const input = this.previousElementSibling;
				
				if (input && input.tagName === 'INPUT') {
					if (input.type === 'password') {
						input.type = 'text';
						this.textContent = nthNotifications.i18n.hide;
					} else {
						input.type = 'password';
						this.textContent = nthNotifications.i18n.show;
					}
				}
			});
		});
	}

	/**
	 * Add Telegram Chat ID field
	 */
	function initAddChatId() {
		// Telegram
		const addTelegramBtn = document.querySelector('.nth-notify__add-chat-id');
		if (addTelegramBtn) {
			addTelegramBtn.addEventListener('click', function(e) {
				e.preventDefault();
				
				const container = document.querySelector('.nth-notify__chat-ids');
				if (!container) return;
				
				const rows = container.querySelectorAll('.nth-notify__chat-row');
				const count = rows.length + 1;
				
				const newRow = document.createElement('div');
				newRow.className = 'nth-notify__chat-row';
				newRow.innerHTML = `
					<label>Chat ID #${count}</label>
					<input type="text" 
						   name="nth_notifications_telegram[chat_ids][]" 
						   class="regular-text nth-notify__chat-id-input" 
						   value="" />
					<button type="button" 
							class="button button-secondary nth-notify__test-chat-id" 
							data-index="${count}">
						${nthNotifications.i18n.test}
					</button>
					<button type="button" 
							class="button button-secondary nth-notify__remove-chat-id">
						${nthNotifications.i18n.remove}
					</button>
					<span class="nth-notify__test-result"></span>
				`;
				
				container.appendChild(newRow);
			});
		}

		// Zalo
		const addZaloBtn = document.querySelector('.nth-notify__add-zalo-chat-id');
		if (addZaloBtn) {
			addZaloBtn.addEventListener('click', function(e) {
				e.preventDefault();
				
				const container = document.querySelector('.nth-notify__zalo-chat-ids');
				if (!container) return;
				
				const rows = container.querySelectorAll('.nth-notify__chat-row');
				const count = rows.length + 1;
				
				const newRow = document.createElement('div');
				newRow.className = 'nth-notify__chat-row';
				newRow.innerHTML = `
					<label>Chat ID #${count}</label>
					<input type="text" 
						   name="nth_notifications_zalo[chat_ids][]" 
						   class="regular-text nth-notify__zalo-chat-id-input" 
						   value="" />
					<button type="button" 
							class="button button-secondary nth-notify__test-zalo" 
							data-index="${count}">
						${nthNotifications.i18n.test}
					</button>
					<button type="button" 
							class="button button-secondary nth-notify__remove-chat-id">
						${nthNotifications.i18n.remove}
					</button>
					<span class="nth-notify__test-result"></span>
				`;
				
				container.appendChild(newRow);
			});
		}
	}

	/**
	 * Remove Chat ID field (event delegation)
	 */
	function initRemoveChatId() {
		document.addEventListener('click', function(e) {
			if (e.target && e.target.classList.contains('nth-notify__remove-chat-id')) {
				e.preventDefault();
				
				const row = e.target.closest('.nth-notify__chat-row');
				if (!row) return;
				
				const container = row.parentElement;
				const rowCount = container.querySelectorAll('.nth-notify__chat-row').length;
				
				if (rowCount > 1) {
					row.remove();
					
					// Renumber labels
					const rows = container.querySelectorAll('.nth-notify__chat-row');
					rows.forEach(function(r, index) {
						const label = r.querySelector('label');
						if (label) {
							label.textContent = 'Chat ID #' + (index + 1);
						}
					});
				} else {
					alert(nthNotifications.i18n.atLeastOneChatId);
				}
			}
		});
	}

	/**
	 * Test Telegram Chat ID (event delegation)
	 */
	function initTestTelegram() {
		document.addEventListener('click', function(e) {
			if (e.target && e.target.classList.contains('nth-notify__test-chat-id')) {
				e.preventDefault();
				
				const button = e.target;
				const row = button.closest('.nth-notify__chat-row');
				if (!row) return;
				
				const input = row.querySelector('.nth-notify__chat-id-input');
				const result = row.querySelector('.nth-notify__test-result');
				const chatId = input.value.trim();
				const botTokenInput = document.getElementById('telegram_bot_token');
				const botToken = botTokenInput ? botTokenInput.value.trim() : '';
				
				if (!chatId) {
					result.innerHTML = `<span style="color: #d63638;">❌ ${nthNotifications.i18n.pleaseEnterChatId}</span>`;
					return;
				}
				
				if (!botToken) {
					result.innerHTML = `<span style="color: #d63638;">❌ ${nthNotifications.i18n.pleaseEnterBotToken}</span>`;
					return;
				}
				
				// Disable button and show loading
				button.disabled = true;
				button.textContent = nthNotifications.i18n.testing;
				result.innerHTML = `<span style="color: #50575e;">⏳ ${nthNotifications.i18n.sendingTestMessage}</span>`;
				
				// Send AJAX request
				sendAjaxRequest({
					action: 'nth_test_telegram',
					bot_token: botToken,
					chat_id: chatId
				})
				.then(function(response) {
					if (response.success) {
						result.innerHTML = `<span style="color: #00a32a;">✅ ${response.data.message}</span>`;
						setTimeout(function() {
							fadeOut(result, function() {
								result.innerHTML = '';
								result.style.opacity = '1';
							});
						}, 5000);
					} else {
						result.innerHTML = `<span style="color: #d63638;">❌ ${response.data.message}</span>`;
					}
				})
				.catch(function() {
					result.innerHTML = `<span style="color: #d63638;">❌ ${nthNotifications.i18n.connectionError}</span>`;
				})
				.finally(function() {
					button.disabled = false;
					button.textContent = nthNotifications.i18n.test;
				});
			}
		});
	}

	/**
	 * Test Zalo Chat ID (event delegation)
	 */
	function initTestZalo() {
		document.addEventListener('click', function(e) {
			if (e.target && e.target.classList.contains('nth-notify__test-zalo')) {
				e.preventDefault();
				
				const button = e.target;
				const row = button.closest('.nth-notify__chat-row');
				if (!row) return;
				
				const input = row.querySelector('.nth-notify__zalo-chat-id-input');
				const result = row.querySelector('.nth-notify__test-result');
				const chatId = input.value.trim();
				const botTokenInput = document.getElementById('zalo_bot_token');
				const botToken = botTokenInput ? botTokenInput.value.trim() : '';
				
				if (!chatId) {
					result.innerHTML = `<span style="color: #d63638;">❌ ${nthNotifications.i18n.pleaseEnterChatId}</span>`;
					return;
				}
				
				if (!botToken) {
					result.innerHTML = `<span style="color: #d63638;">❌ ${nthNotifications.i18n.pleaseEnterBotToken}</span>`;
					return;
				}
				
				// Disable button and show loading
				button.disabled = true;
				button.textContent = nthNotifications.i18n.testing;
				result.innerHTML = `<span style="color: #50575e;">⏳ ${nthNotifications.i18n.sendingTestMessage}</span>`;
				
				// Send AJAX request
				sendAjaxRequest({
					action: 'nth_test_zalo',
					bot_token: botToken,
					chat_id: chatId
				})
				.then(function(response) {
					if (response.success) {
						result.innerHTML = `<span style="color: #00a32a;">✅ ${response.data.message}</span>`;
						setTimeout(function() {
							fadeOut(result, function() {
								result.innerHTML = '';
								result.style.opacity = '1';
							});
						}, 5000);
					} else {
						result.innerHTML = `<span style="color: #d63638;">❌ ${response.data.message}</span>`;
					}
				})
				.catch(function() {
					result.innerHTML = `<span style="color: #d63638;">❌ ${nthNotifications.i18n.connectionError}</span>`;
				})
				.finally(function() {
					button.disabled = false;
					button.textContent = nthNotifications.i18n.test;
				});
			}
		});
	}

	/**
	 * Find Zalo Chat ID
	 */
	function initFindZaloChatId() {
		const findButton = document.querySelector('.nth-notify__find-zalo-chat-id');
		
		if (findButton) {
			findButton.addEventListener('click', function(e) {
				e.preventDefault();
				
				const botTokenInput = document.getElementById('zalo_bot_token');
				const botToken = botTokenInput ? botTokenInput.value.trim() : '';
				
				if (!botToken) {
					alert(nthNotifications.i18n.pleaseEnterBotToken);
					if (botTokenInput) {
						botTokenInput.focus();
					}
					return;
				}
				
				this.disabled = true;
				this.textContent = nthNotifications.i18n.waitingForMessage;
				
				sendAjaxRequest({
					action: 'nth_find_zalo_chat_id',
					bot_token: botToken
				})
				.then(function(response) {
					if (response.success) {
						const newId = response.data.chat_id;
						
						// Check if ID already exists
						const inputs = document.querySelectorAll('.nth-notify__zalo-chat-id-input');
						let exists = false;
						let emptyField = null;
						
						// Check for existing ID and find empty field in one loop
						inputs.forEach(function(input) {
							if (input.value.trim() === newId) {
								exists = true;
							}
							// Find first empty field
							if (!emptyField && input.value.trim() === '') {
								emptyField = input;
							}
						});
						
						if (exists) {
							alert(`${nthNotifications.i18n.chatIdFound} ${newId}\n\n${nthNotifications.i18n.chatIdExists}`);
						} else if (emptyField) {
							// Use existing empty field
							emptyField.value = newId;
							alert(`${nthNotifications.i18n.chatIdFoundAndAdded} ${newId}`);
						} else {
							// No empty field - add new one
							const addButton = document.querySelector('.nth-notify__add-zalo-chat-id');
							if (addButton) {
								addButton.click();
								// Wait for DOM to update
								setTimeout(function() {
									// Query again to get the newly created field
									const allInputs = document.querySelectorAll('.nth-notify__zalo-chat-id-input');
									const lastInput = allInputs[allInputs.length - 1];
									if (lastInput) {
										lastInput.value = newId;
										alert(`${nthNotifications.i18n.chatIdFoundAndAdded} ${newId}`);
									}
								}, 150);
							}
						}
					} else {
						const errorMsg = response.data.message;
						if (errorMsg.indexOf('No messages found') !== -1 || errorMsg.indexOf('Không tìm thấy') !== -1) {
							alert(nthNotifications.i18n.noMessageFound);
						} else {
							alert(`${nthNotifications.i18n.error} ${errorMsg}`);
						}
					}
				})
				.catch(function() {
					alert(nthNotifications.i18n.connectionError);
				})
				.finally(function() {
					findButton.disabled = false;
					findButton.textContent = nthNotifications.i18n.findChatId;
				});
			});
		}
	}

	/**
	 * Send AJAX request
	 * 
	 * @param {Object} data Request data
	 * @return {Promise}
	 */
	function sendAjaxRequest(data) {
		return new Promise(function(resolve, reject) {
			const xhr = new XMLHttpRequest();
			
			// Prepare form data
			const formData = new FormData();
			formData.append('nonce', nthNotifications.nonce);
			
			for (const key in data) {
				if (data.hasOwnProperty(key)) {
					formData.append(key, data[key]);
				}
			}
			
			xhr.open('POST', nthNotifications.ajax_url, true);
			
			xhr.onload = function() {
				if (xhr.status >= 200 && xhr.status < 400) {
					try {
						const response = JSON.parse(xhr.responseText);
						resolve(response);
					} catch (e) {
						reject(new Error('Invalid JSON response'));
					}
				} else {
					reject(new Error('HTTP Error: ' + xhr.status));
				}
			};
			
			xhr.onerror = function() {
				reject(new Error('Network Error'));
			};
			
			xhr.send(formData);
		});
	}

	/**
	 * Fade out element
	 * 
	 * @param {HTMLElement} element Element to fade out
	 * @param {Function} callback Callback after fade out
	 */
	function fadeOut(element, callback) {
		let opacity = 1;
		const timer = setInterval(function() {
			if (opacity <= 0.1) {
				clearInterval(timer);
				element.style.opacity = '0';
				if (callback) {
					callback();
				}
			}
			element.style.opacity = opacity;
			opacity -= opacity * 0.1;
		}, 50);
	}

})();
