Vue.use(VueMaterial.default);

function Api(param) {
	return new Promise(resolve => {
		const fetchParam = {
			method: param.post ? 'POST' : 'GET',
			headers: {
				'X-Admin-Password': sessionStorage.getItem('wechat_admin')
			}
		};
		let url = `${BASE_URI}${param.url}`;
		if (param.query) {
			url += '?' + (new URLSearchParams(param.query)).toString();
		}
		if (fetchParam.method === 'POST') {
			fetchParam.headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
			const formBody = [];
			for (const property in param.post) {
				const encodedKey = encodeURIComponent(property);
				const encodedValue = encodeURIComponent(param.post[property]);
  				formBody.push(encodedKey + "=" + encodedValue);
			}
			fetchParam.body = formBody.join("&");
		}
		fetch(url, fetchParam)
		.then(r => r.json())
		.then(resolve);
	})
}

new Vue({
	el: '#app',
	data: function() {
		return {
			menuVisible: true,
			status: 0,
			activePage: "config",
			password: "",
			config: [],
			app: [],
			toast: {
				show: false,
				message: ""
			},
			newPassword: {
				show: false,
				value: ""
			},
			edit_app: {
				show: false,
				id: -1,
				name: "",
				appid: "",
				appsecret: "",
				type: 1
			},
			show_secret: {
				show: false,
				content: ""
			}
		};
	},
	computed: {
		showLogin: function() {
			return this.status < 2;
		}
	},
	methods: {
		showToast: function(msg) {
			this.toast.message = msg;
			this.toast.show = true;
		},
		switchPage: function(name) {
			this.activePage = name;
		},
		tryLogin: function() {
			const _this = this;
			_this.status = 0;
			Api({
				url: 'index/admin/login',
				post: {
					password: this.password
				}
			})
			.then(r => {
				if (r.success) {
					sessionStorage.setItem('wechat_admin', r.password);
					_this.status = 2;
					_this.showToast('登录成功');
				} else {
					_this.status = 1;
					_this.showToast(r.error);
				}
			});
		},
		loadConfig: function() {
			const _this = this;
			Api({
				url: 'admin/config/list'
			})
			.then(r => {
				if (r.success) {
					_this.config = r.list
				}
			});
		},
		saveConfig: function() {
			const queue = [];
			const _this = this;
			_this.config.forEach(e => {
				queue.push(Api({
					url: 'admin/config/save',
					post: {
						id: e.id,
						value: e.value
					}
				}));
			});
			Promise.all(queue)
			.then(r => {
				_this.showToast('已保存');
			});
		},
		changePassword: function() {
			this.newPassword.show = true;
		},
		changePasswordSubmit: function() {
			const _this = this;
			Api({
				url: 'admin/config/password',
				post: {
					password: _this.newPassword.value
				}
			})
			.then(r => {
				_this.newPassword.value = "";
				if (r.success) {
					sessionStorage.setItem('wechat_admin', r.password);
					_this.showToast('密码修改成功');
				} else {
					_this.showToast(r.error);
				}
			});
		},
		loadApp: function() {
			const _this = this;
			Api({
				url: 'admin/app/list'
			})
			.then(r => {
				if (r.success) {
					_this.app = r.list;
				}
			});
		},
		showSecret: function(secret) {
			this.show_secret.content = secret;
			this.show_secret.show = true;
		},
		editApp: function(app) {
			if (app === null) {
				this.edit_app.id = -1;
				this.edit_app.name = "";
				this.edit_app.appid = "";
				this.edit_app.appsecret = "";
				this.edit_app.type = 1;
			} else {
				this.edit_app.id = app.id;
				this.edit_app.name = app.name;
				this.edit_app.appid = app.appid;
				this.edit_app.appsecret = app.appsecret;
				this.edit_app.type = parseInt(app.type);
			}
			this.edit_app.show = true;
		},
		editAppSave: function() {
			const _this = this;
			Api({
				url: 'admin/app/save',
				post: _this.edit_app
			})
			.then(r => {
				if (r.success) {
					_this.edit_app.show = false;
					_this.showToast('已保存');
					_this.loadApp();
				}
			})
		},
		delApp: function(app) {
			const _this = this;
			if (confirm(`您确定要删除应用“${app.name}”吗？此操作不可撤销`)) {
				Api({
					url: 'admin/app/del',
					post: {
						id: app.id
					}
				})
				.then(r => {
					if (r.success) {
						_this.showToast(`已删除应用“${app.name}”`);
						_this.loadApp();
					}
				})
			}
		},
		loginout: function() {
			sessionStorage.removeItem('wechat_admin');
			this.status = 1;
		}
	},
	mounted: function() {
		const _this = this;
		//检查登录状态
		Api({
			url: 'admin/index/status'
		})
		.then(r => {
			if (r.success) {
				_this.status = 2;
			} else {
				_this.status = 1;
			}
		})
	}
});