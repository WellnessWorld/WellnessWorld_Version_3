document.getElementsByTagName("html")[0].className += ' mailster-loading';
jQuery(document).ready(function ($) {

	"use strict"

	var html = $('html'),
		body = $('body'),
		uploader, container, modules, images, buttons, repeatable, selection,
		currentmodule,
		isTinymce = typeof tinymce != 'undefined';

	//not in an iframe
	if (parent.window == window) {

		var campaign_id;
		if (campaign_id = location.search.match(/id=(\d+)/i)[1]) {
			window.location = location.protocol + '//' + location.host + location.pathname.replace('admin-ajax.php', 'post.php') + '?post=' + campaign_id + '&action=edit';
		}
	}

	window.ajaxurl = window.ajaxurl || window.mailsterdata.ajaxurl;

	$(window).on('load', function () {

		body
			.on('click', 'a', function (event) {
				event.preventDefault();
			})
			.on('click', function (event) {
				if (currentmodule) {
					currentmodule.removeAttr('selected');
				}
			})
			.on('click', 'module', function (event) {
				if ('MODULE' == event.target.nodeName) {
					event.stopPropagation();
					_trigger('selectModule', $(this));
				}
			})
			.on('click', 'button.addbutton', function () {
				var data = $(this).data(),
					element = decodeURIComponent(data.element.data('tmpl')) || '<a href="" editable label="Button"></a>';

				parent.window.Mailster.editbar.open({
					type: 'btn',
					offset: data.offset,
					element: $(element).appendTo(data.element),
					name: data.name
				});
				return false;
			})
			.on('click', 'button.addrepeater', function () {
				var data = $(this).data();

				data.element.clone().insertAfter(data.element);
				_trigger('save');
				_trigger('refresh');

				return false;
			})
			.on('click', 'button.removerepeater', function () {
				var data = $(this).data();

				data.element.remove();
				_trigger('save');
				_trigger('refresh');

				return false;
			});

		//legacy buttons
		body.find('div.modulebuttons').remove();
		(mailsterdata.isrtl) ? html.attr('mailster-is-rtl', ''): html.removeAttr('mailster-is-rtl');

	});

	function _refresh() {

		container = $('modules');
		modules = container.find('module');
		images = $('img[editable]');
		buttons = $('buttons');
		repeatable = $('[repeatable]');

		_sortable();
		_draggable();
		_upload();
		if (isTinymce) _inlineeditor();

		html.removeClass('mailster-loading');

	}

	function _resize() {
		_buttons();
	}

	function _inlineeditor() {

		var l10n = mailster_mce_button.l10n,
			tags = mailster_mce_button.tags,
			designs = mailster_mce_button.designs,
			tiny = mailsterdata.tinymce,
			changetimeout,
			change = false;

		tinymce.init($.extend(tiny.args, tiny.multi, {
			urlconverter_callback: _urlconverter,
			setup: _setup
		}));
		tinymce.init($.extend(tiny.args, tiny.single, {
			urlconverter_callback: _urlconverter,
			setup: _setup
		}));

		function _urlconverter(url, node, on_save, name) {
			if ('_wp_link_placeholder' == url) {
				return url;
			} else if (/^https?:\/\/{.+}/g.test(url)) {
				return url.replace(/^https?:\/\//, '');
			} else if (/^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i.test(url)) {
				return 'mailto:' + url;
			}
			return this.documentBaseURI.toAbsolute(url, tiny.remove_script_host);
		}

		function _setup(editor) {

			editor.addButton('mailster_mce_button', {
				title: l10n.tags.title,
				type: 'menubutton',
				icon: 'icon mailster-tags-icon',
				menu: $.map(tags, function (group, id) {
					return {
						text: group.name,
						menu: $.map(group.tags, function (name, tag) {
							return {
								text: name,
								onclick: function () {
									var poststuff = '';
									switch (tag) {
									case 'webversion':
									case 'unsub':
									case 'forward':
									case 'profile':
										poststuff = 'link';
									case 'homepage':
										if (selection = editor.selection.getContent({
												format: "text"
											})) {
											editor.insertContent('<a href="{' + tag + poststuff + '}">' + selection + '</a>');
											break;
										}
									default:
										editor.insertContent('{' + tag + '} ');
									}
								}
							};

						})
					};
				})
			});

			editor.addButton('mailster_remove_element', {
				title: l10n.remove.title,
				icon: 'icon mailster-remove-icon',
				onclick: function () {
					editor.targetElm.remove();
					editor.remove();
					_trigger('save');
				}
			});

			editor
				.on('change', function (event) {
					var _self = this;
					clearTimeout(changetimeout);
					changetimeout = setTimeout(function () {
						var content = event.level.content,
							c = content.match(/rgb\((\d+), ?(\d+), ?(\d+)\)/g);
						if (c) {
							for (var i = c.length - 1; i >= 0; i--) {
								content = content.replace(c[i], _hex(c[i]));
							}
							_self.bodyElement.innerHTML = content;
						}
						_trigger('save');
						change = true;
					}, 100)
				})
				.on('keyup', function (event) {
					$(event.currentTarget).prop('spellcheck', true);
				})
				.on('click', function (event) {
					_trigger('selectModule', $(event.currentTarget).closest('module'));
					event.stopPropagation();
					editor.focus();
				})
				.on('focus', function (event) {
					event.stopPropagation();
					editor.selection.select(editor.getBody(), true);
					if (container.data('uiSortable')) container.sortable('destroy');
				})
				.on('blur', function (event) {
					_trigger('refresh');
					_sortable();
				});

		}

	}

	function _hex(str) {
		var colors = str.match(/rgb\((\d+), ?(\d+), ?(\d+)\)/);

		function hex(val) {
			val = parseInt(val, 10).toString(16);

			return val.length > 1 ? val : '0' + val; // 0 -> 00
		}
		return colors ? '#' + hex(colors[1]) + hex(colors[2]) + hex(colors[3]) : str;

	}

	function _sortable() {

		if (container.data('sortable')) container.sortable('destroy');
		// if (buttons.data('sortable')) buttons.sortable('destroy');

		// buttons.sortable({
		// 	stop: function (event, ui) {
		// 		event.stopPropagation();
		// 		container.removeClass('dragging');
		// 		setTimeout(function () {
		// 			_trigger('refresh');
		// 		}, 200);
		// 	},
		// 	start: function (event, ui) {
		// 		event.stopPropagation();
		// 		_trigger('hidebuttons');
		// 		container.addClass('dragging');
		// 	},
		// 	containment: 'parent',
		// 	revert: 100,
		// 	placeholder: "sortable-placeholder",
		// 	items: "> a",
		// 	distance: 5,
		// 	forcePlaceholderSize: true,
		// 	helper: 'clone',
		// 	zIndex: 10000
		// });


		if (modules.length < 2) return;

		container.sortable({
			stop: function (event, ui) {
				event.stopPropagation();
				container.removeClass('dragging');
				//_trigger('selectModule', ui.item);
				setTimeout(function () {
					_trigger('refresh');
					_trigger('save');
				}, 200);
			},
			start: function (event, ui) {
				event.stopPropagation();
				container.addClass('dragging');
			},
			containment: 'body',
			revert: 100,
			axis: 'y',
			placeholder: "sortable-placeholder",
			items: "> module",
			delay: 20,
			distance: 5,
			scroll: true,
			scrollSensitivity: 10,
			forcePlaceholderSize: true,
			helper: 'clone',
			zIndex: 10000

		});

	}

	function _draggable() {

		if (images.data('draggable')) images.draggable('destroy');
		if (images.data('droppable')) images.droppable('destroy');

		images
			.draggable({
				helper: "clone",
				scroll: true,
				scrollSensitivity: 10,
				opacity: 0.7,
				zIndex: 1000,
				revert: 'invalid',
				addClasses: false,
				create: function (event, ui) {
					$(event.target).removeClass('ui-draggable-handle');
				},
				start: function () {
					body.addClass('ui-dragging');
				},
				stop: function () {
					body.removeClass('ui-dragging');
					_trigger('refresh');

				}
			})
			.droppable({
				addClasses: false,
				over: function (event, ui) {
					$(event.target).addClass('ui-drag-over');
				},
				out: function (event, ui) {
					$(event.target).removeClass('ui-drag-over');
				},
				drop: function (event, ui) {
					var org = $(ui.draggable[0]),
						target = $(event.target),
						target_id, org_id, copy;

					target.removeClass('ui-drag-over');

					if (!org.is('img') || !target.is('img')) return;

					target_id = target.attr('data-id') ? parseInt(target.attr('data-id'), 10) : null;
					org_id = org.attr('data-id') ? parseInt(org.attr('data-id'), 10) : null;
					copy = org.clone();

					org.addClass('mailster-loading');
					target.addClass('mailster-loading');

					_getRealDimensions(org, function (org_w, org_h, org_f) {
						_getRealDimensions(target, function (target_w, target_h, target_f) {

							if (event.altKey) {
								org.removeClass('mailster-loading');
								target.removeClass('mailster-loading');
							} else if (target_id) {
								_ajax('create_image', {
									id: target_id,
									width: org_w,
									_height: org_h
								}, function (response) {

									org.removeAttr('src').attr({
										'data-id': target_id,
										'title': target.attr('title'),
										'alt': target.attr('alt'),
										'src': response.image.url,
										'width': Math.round(response.image.width / org_f),
										'height': Math.round(response.image.height / org_f)
									}).data('id', target_id).removeClass('mailster-loading');

								}, function (jqXHR, textStatus, errorThrown) {

									alert(textStatus + ' ' + jqXHR.status + ': ' + errorThrown + '\n\nCheck the JS console for more info!');

								});
							} else {

								org.removeAttr('src').attr({
									'data-id': 0,
									'title': target.attr('title'),
									'alt': target.attr('alt'),
									'src': target.attr('src'),
									'width': Math.round(org_w / org_f),
									//'height': Math.round(org_h/org_f)
									'height': Math.round((org_w / (target_w / target_h)) / org_f)
								}).data('id', 0).removeClass('mailster-loading');

							}

							if (org_id) {
								_ajax('create_image', {
									id: org_id,
									width: target_w,
									_height: target_h
								}, function (response) {

									target.removeAttr('src').attr({
										'data-id': org_id,
										'title': org.attr('title'),
										'alt': org.attr('alt'),
										'src': response.image.url,
										'width': Math.round(response.image.width / target_f),
										'height': Math.round(response.image.height / target_f)
									}).data('id', org_id).removeClass('mailster-loading');

									_trigger('refresh');

								}, function (jqXHR, textStatus, errorThrown) {

									alert(textStatus + ' ' + jqXHR.status + ': ' + errorThrown + '\n\nCheck the JS console for more info!');

								});
							} else {

								target.removeAttr('src').attr({
									'data-id': 0,
									'title': copy.attr('title'),
									'alt': copy.attr('alt'),
									'src': copy.attr('src'),
									'width': Math.round(target_w / target_f),
									'height': Math.round((target_w / (org_w / org_h)) / target_f)
								}).data('id', 0).removeClass('mailster-loading');

							}

							if (!org_id && !target_id) _trigger('refresh');

						});
					});

				}
			});

	}

	function _buttons() {

		if (buttons) {
			$.each(buttons, function () {

				var $this = $(this),
					name = $this.attr('label'),
					offset = this.getBoundingClientRect(),
					top = offset.top + 0,
					left = offset.right + 0,
					btn, tmpl;

				if ($this.data('has-buttons')) return;

				btn = $('<button class="addbutton mailster-btn-inline mailster-icon" title="' + mailsterL10n.add_button + '"></button>').prependTo($this);

				btn.data('offset', offset).data('name', name);
				btn.data('element', $this);

				$this.data('has-buttons', true);

				if (!(tmpl = $this.data('tmpl'))) {
					if ($this.find('.textbutton').length) {
						tmpl = $this.find('.textbutton').last();
					} else if ($this.find('img').length) {
						tmpl = $this.find('a[editable]').last();
					} else {
						tmpl = $('<a href="" editable label="Button"></a>');
					}
					tmpl = $('<div/>').text(encodeURIComponent(tmpl[0].outerHTML)).html();
				}

				$this.attr('data-tmpl', tmpl).data('tmpl', tmpl);

			});
		}

		$('button.addrepeater, button.removerepeater').remove();

		if (repeatable) {
			$.each(repeatable, function () {
				var $this = $(this),
					module = $this.closest('module'),
					name = $this.attr('label'),
					moduleoffset = module[0].getBoundingClientRect(),
					offset = this.getBoundingClientRect(),
					top = offset.top - moduleoffset.top,
					left = offset.right,
					btn;

				btn = $('<button class="addrepeater mailster-btn-inline mailster-icon" title=""></button>').css({
					top: top,
					left: left
				}).appendTo(module);

				btn.data('offset', offset).data('name', name);
				btn.data('element', $this);

				btn = $('<button class="removerepeater mailster-btn-inline mailster-icon" title=""></button>').css({
					top: top + 20,
					left: left
				}).appendTo(module);

				btn.data('offset', offset).data('name', name);
				btn.data('element', $this);

			});
		}

	}

	function _upload() {

		if (typeof mOxie != 'undefined') {

			$.each(images, function () {

				var _this = $(this),
					dropzone;

				if (_this.data('has-dropzone')) return;

				dropzone = new mOxie.FileDrop({
					drop_zone: this,
				});

				dropzone.ondrop = function (e) {

					if (parent.window.mailster_is_modulde_dragging) return;
					_this.removeClass('ui-drag-over-file ui-drag-over-file-alt');

					var file = dropzone.files.shift(),
						altkey = window.event && event.altKey,
						dimensions = [_this.width(), _this.height()],
						position = _this.offset(),
						preview = $('<div class="mailster-upload-info"><div class="mailster-upload-info-bar"></div><div class="mailster-upload-info-text"></div></div>').css({
							width: dimensions[0],
							height: dimensions[1],
							top: position.top,
							left: position.left

						}),

						preloader = new mOxie.Image(file);

					preloader.onerror = function (e) {

						alert(mailsterL10n.unsupported_format);

					}
					preloader.onload = function (e) {

						preview.appendTo('body');

						file._element = _this;
						file._altKey = altkey;
						file._preview = preview;
						file._dimensions = [preloader.width, preloader.height, preloader.width / preloader.height];

						preloader.downsize(dimensions[0], dimensions[1]);
						preview.find('.mailster-upload-info-bar').css({
							'background-image': 'url(' + preloader.getAsDataURL() + ')',
							'background-size': dimensions[0] + 'px ' + (dimensions[0] / file._dimensions[2]) + 'px'
						});

						uploader.addFile(file);
					};

					preloader.load(file);

				};
				dropzone.ondragenter = function (e) {
					if (parent.window.mailster_is_modulde_dragging) return;
					_this.addClass('ui-drag-over-file');
					if (window.event && event.altKey) _this.addClass('ui-drag-over-file-alt');
				};
				dropzone.ondragleave = function (e) {
					if (parent.window.mailster_is_modulde_dragging) return;
					_this.removeClass('ui-drag-over-file ui-drag-over-file-alt');
				};
				dropzone.onerror = function (e) {
					if (parent.window.mailster_is_modulde_dragging) return;
					_this.removeClass('ui-drag-over-file ui-drag-over-file-alt');
				};

				dropzone.init();

				_this.data('has-dropzone', true);

			});


			if (!uploader) {


				$('<button id="mailster-editorimage-upload-button" />').hide().appendTo('body');
				uploader = new plupload.Uploader(mailsterdata.plupload);

				uploader.bind('Init', function (up) {
					$('.moxie-shim').remove();
				});

				uploader.bind('FilesAdded', function (up, files) {

					var source = files[0].getSource();

					_getRealDimensions(source._element, function (width, height, factor) {

						up.settings.multipart_params.width = width;
						up.settings.multipart_params.height = height;
						up.settings.multipart_params.factor = factor;
						up.settings.multipart_params.altKey = source._altKey;
						up.refresh();
						up.start();
					});

				});

				uploader.bind('BeforeUpload', function (up, file) {});

				uploader.bind('UploadFile', function (up, file) {});

				uploader.bind('UploadProgress', function (up, file) {

					var source = file.getSource();

					source._preview.find('.mailster-upload-info-bar').width(file.percent + '%');
					source._preview.find('.mailster-upload-info-text').html(file.percent + '%');

				});

				uploader.bind('Error', function (up, err) {
					var source = err.file.getSource();

					alert(err.message);

					source._preview.remove();
				});

				uploader.bind('FileUploaded', function (up, file, response) {

					var source = file.getSource(),
						delay, height;

					try {
						response = $.parseJSON(response.response);

						source._preview.find('.mailster-upload-info-text').html(mailsterL10n.ready);
						source._element.on('load', function () {
							clearTimeout(delay);
							source._preview.fadeOut(function () {
								$(this).remove();
								_trigger('refresh');
							});
						});

						height = Math.round(source._element.width() / response.image.asp);

						source._element.attr({
							'src': response.image.url,
							//'height': Math.round(response.image.height/up.settings.multipart_params.factor),
							'height': height,
							'data-id': response.image.id || 0
						}).data('id', response.image.id || 0);

						source._preview.height(height);

						delay = setTimeout(function () {
							source._preview.fadeOut(function () {
								$(this).remove();
								_trigger('refresh');
							});
						}, 3000);
					} catch (err) {
						source._preview.addClass('error').find('.mailster-upload-info-text').html(mailsterL10n.error);
						alert(mailsterL10n.error_occurs + "\n" + err.message);
						source._preview.fadeOut(function () {
							$(this).remove();
						});
					}

				});

				uploader.bind('UploadComplete', function (up, files) {});

				uploader.init();

			}

		}

	}


	$(window)
		.on('Mailster:refresh', function () {
			_refresh();
		})
		.on('Mailster:resize', function () {
			_resize();
		})
		.on('Mailster:selectModule', function (event) {
			if (!event.detail) return;
			var module = $(event.detail[0]);
			if (currentmodule) {
				currentmodule.removeAttr('selected');
			}
			currentmodule = module;
			currentmodule.attr('selected', true);
		})

	function _trigger() {
		if (!window.Mailster) {
			window.Mailster = parent.window.Mailster;
		}
		if (!window.Mailster) return;
		var args = jQuery.makeArray(arguments);
		var triggerevent = args.shift();
		window.Mailster.trigger(triggerevent, args);
	}

	function _getRealDimensions(el, callback) {
		el = el.eq(0);
		if (el.is('img') && el.attr('src') && callback) {
			var image = new Image(),
				factor;
			image.onload = function () {
				factor = Math.max(1, Math.min(2, ((image.width / (el.attr('width') || el.width())).toFixed(1) || 1)));
				callback.call(this, image.width, image.height, isFinite(factor) ? parseFloat(factor) : 1)
			}
			image.src = el.attr('src');
		}
	}

	function _ajax(action, data, callback, errorCallback, dataType) {

		if ($.isFunction(data)) {
			if ($.isFunction(callback)) {
				errorCallback = callback;
			}
			callback = data;
			data = {};
		}
		$.ajax({
			type: 'POST',
			url: window.mailsterdata.ajaxurl,
			data: $.extend({
				action: 'mailster_' + action,
				_wpnonce: window.mailsterdata._wpnonce
			}, data),
			success: function (data, textStatus, jqXHR) {
				callback && callback.call(this, data, textStatus, jqXHR);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				if (textStatus == 'error' && !errorThrown) return;
				if (console) console.error($.trim(jqXHR.responseText));
				errorCallback && errorCallback.call(this, jqXHR, textStatus, errorThrown);

			},
			dataType: dataType ? dataType : "JSON"
		});
	}

	function sprintf() {
		var a = Array.prototype.slice.call(arguments),
			str = a.shift(),
			total = a.length,
			reg;
		for (var i = 0; i < total; i++) {
			reg = new RegExp('%(' + (i + 1) + '\\$)?(s|d|f)');
			str = str.replace(reg, a[i]);
		}
		return str;
	}

});