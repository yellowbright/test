(function () {
  'use strict';

  // 移动端汉堡菜单
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('primaryNav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    nav.addEventListener('click', function (e) {
      if (e.target.tagName === 'A') {
        nav.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  // 联系表单 AJAX 提交
  var form = document.getElementById('contactForm');
  if (!form) return;

  var alertBox = form.querySelector('.form-alert');
  var submitBtn = form.querySelector('button[type="submit"]');
  var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  function clearErrors() {
    form.querySelectorAll('.field.has-error').forEach(function (f) {
      f.classList.remove('has-error');
      var err = f.querySelector('.err');
      if (err) err.textContent = '';
    });
    if (alertBox) alertBox.className = 'form-alert';
  }

  function showErrors(errors) {
    Object.keys(errors).forEach(function (name) {
      var field = form.querySelector('[name="' + name + '"]');
      if (!field) return;
      var wrap = field.closest('.field');
      if (!wrap) return;
      wrap.classList.add('has-error');
      var err = wrap.querySelector('.err');
      if (err) err.textContent = errors[name][0];
    });
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();
    submitBtn.disabled = true;

    fetch(form.action, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': token,
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(Object.fromEntries(new FormData(form))),
    })
      .then(function (res) {
        return res.json().then(function (data) {
          return { ok: res.ok, status: res.status, data: data };
        });
      })
      .then(function (r) {
        if (r.ok) {
          form.reset();
          alertBox.textContent = r.data.message || '提交成功';
          alertBox.className = 'form-alert ok';
        } else if (r.status === 422) {
          showErrors(r.data.errors || {});
          alertBox.textContent = '请检查表单填写。';
          alertBox.className = 'form-alert fail';
        } else {
          throw new Error('request failed');
        }
      })
      .catch(function () {
        alertBox.textContent = '提交失败，请稍后再试。';
        alertBox.className = 'form-alert fail';
      })
      .finally(function () {
        submitBtn.disabled = false;
      });
  });
})();
