function Api(param) {
	return new Promise(resolve => {
		const fetchParam = {
			method: param.post ? 'POST' : 'GET',
			credentials: "same-origin"
		};
		let url = `${BASE_URI}${param.url}`;
		if (param.query) {
			url += '?' + (new URLSearchParams(param.query)).toString();
		}
		if (fetchParam.method === 'POST') {
			fetchParam.headers = {
				'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
			};
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

//加载设置
function loadSetting() {
	const loading = weui.loading('正在加载设置');
	Api({
		url: 'web/privapi/getSetting'
	})
	.then((r) => {
		document.getElementById('setting-push').checked = r.receive_push == 1;
		loading.hide();
	})
}
function saveSetting() {
	const loading = weui.loading('正在保存');
	Api({
		url: 'web/privapi/setSetting',
		post: {
			"receive_push": document.getElementById('setting-push').checked ? 1 : 0
		}
	})
	.then((r) => {
		loading.hide();
		weui.toast('操作成功', 2500);
	})
}

document.addEventListener('DOMContentLoaded', () => {
	loadSetting();
	document.getElementById('setting-save').addEventListener('click', saveSetting);
});