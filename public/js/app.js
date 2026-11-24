document.addEventListener('DOMContentLoaded',()=>{
 const toggle=document.querySelector('[data-menu-toggle]'), mobile=document.querySelector('[data-mobile-nav]');
 if(toggle&&mobile)toggle.addEventListener('click',()=>{mobile.classList.toggle('hidden');mobile.classList.toggle('flex')});
 document.querySelectorAll('[data-confirm]').forEach(el=>el.addEventListener('click',e=>{if(!confirm(el.dataset.confirm||'Yakin melanjutkan?'))e.preventDefault()}));
 document.querySelectorAll('[data-auto-submit]').forEach(el=>el.addEventListener('change',()=>el.form.submit()));
});
