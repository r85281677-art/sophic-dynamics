const API = 'api.php';
let selectedType = 'flight';
let currentResults = [];

const $ = (id) => document.getElementById(id);
const formObject = (form) => Object.fromEntries(new FormData(form).entries());
const escapeHTML = (value) => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
const money = (amount, currency='NGN') => {
  try { return new Intl.NumberFormat(undefined,{style:'currency',currency}).format(Number(amount)); }
  catch { return `${currency} ${Number(amount).toLocaleString()}`; }
};
async function api(action, data={}, method='POST') {
  const url = method === 'GET' ? `${API}?action=${encodeURIComponent(action)}&${new URLSearchParams(data)}` : `${API}?action=${encodeURIComponent(action)}`;
  const options = {method,headers:{'Accept':'application/json'}};
  if(method !== 'GET'){options.headers['Content-Type']='application/json';options.body=JSON.stringify({...data,action});}
  const response = await fetch(url,options);
  let result;
  try { result = await response.json(); }
  catch { throw new Error('The server did not return JSON. Check PHP errors and the config.php database connection.'); }
  if(!response.ok || !result.success) throw new Error(result.message || 'Request failed.');
  return result;
}
function showMessage(text, error=false){const el=$('message');el.textContent=text;el.className='message'+(error?' error':'');}
document.querySelectorAll('.service-card').forEach(button => button.addEventListener('click',()=>{
  document.querySelectorAll('.service-card').forEach(b=>b.classList.remove('selected'));
  button.classList.add('selected');selectedType=button.dataset.type;
}));
function buildSearch(){
  return {...formObject($('searchForm')),type:selectedType};
}
function serviceCard(s){
  const route=[s.origin,s.destination].filter(Boolean).join(' → ');
  const place=s.location || route || 'Location supplied by provider';
  const dates=s.departure_datetime ? `Departure: ${s.departure_datetime}` :
    (s.check_in_date || s.check_out_date ? `Stay: ${s.check_in_date||'flexible'} – ${s.check_out_date||'flexible'}` : '');
  const image=s.image_url ? `<img src="${escapeHTML(s.image_url)}" alt="" style="width:100%;height:135px;object-fit:cover;border-radius:9px;margin:10px 0" onerror="this.style.display='none'">` : '';
  return `<article class="result-card">
    <div class="result-top"><span class="pill">${escapeHTML(s.service_type.replaceAll('_',' '))}</span><span class="price">${money(s.price,s.currency)}</span></div>
    ${image}<h3>${escapeHTML(s.title)}</h3><p><b>${escapeHTML(s.provider_name)}</b></p>
    <p>${escapeHTML(s.description || '')}</p><p>📍 ${escapeHTML(place)}</p>
    ${dates?`<p>🗓️ ${escapeHTML(dates)}</p>`:''}
    <p>${Number(s.available_quantity)} available listing unit(s)</p>
    <button class="primary" type="button" data-book="${Number(s.id)}" data-title="${escapeHTML(s.title)}">Request booking</button>
  </article>`;
}
async function searchServices(){
  showMessage('Searching available listings…');$('results').innerHTML='';$('resultCount').textContent='…';
  try{
    const result=await api('search',buildSearch(),'GET');
    currentResults=result.data||[];
    $('resultCount').textContent=`${currentResults.length} result(s)`;
    if(!currentResults.length){showMessage('No available listings matched your search. Try another date, location, or service.');$('results').innerHTML='<div class="empty">No matching listings found.</div>';return;}
    showMessage('Results are based on provider listings. Confirm details with the provider before travel.');
    $('results').innerHTML=currentResults.map(serviceCard).join('');
  }catch(e){showMessage(e.message,true);$('resultCount').textContent='0 results';}
}
$('searchForm').addEventListener('submit',e=>{e.preventDefault();searchServices();});
$('saveSearch').addEventListener('click',async()=>{
  try{const r=await api('save_search',buildSearch());alert(r.message);}
  catch(e){alert(e.message);}
});
document.addEventListener('click',e=>{
  const btn=e.target.closest('[data-book]');
  if(!btn)return;
  $('bookingForm').reset();$('bookingServiceId').value=btn.dataset.book;$('bookingTitle').textContent=btn.dataset.title;
  $('bookingDialog').showModal();
});
$('closeDialog').addEventListener('click',()=>$('bookingDialog').close());
$('bookingForm').addEventListener('submit',async e=>{
  e.preventDefault();
  const data=formObject(e.currentTarget);data.service_id=Number(data.service_id);data.quantity=Number(data.quantity);
  try{
    const result=await api('book',data);
    alert(`${result.message}\nReference: ${result.data.reference_code}\nTotal: ${money(result.data.total_price,result.data.currency)}`);
    $('bookingDialog').close();loadBookings();searchServices();
  }catch(err){alert(err.message);}
});
async function loadBookings(){
  $('bookingResults').innerHTML='<p class="muted">Loading bookings…</p>';
  try{
    const r=await api('my_bookings',{},'GET');
    if(!r.data.length){$('bookingResults').innerHTML='<div class="empty">No travel bookings yet.</div>';return;}
    $('bookingResults').innerHTML=r.data.map(b=>`<article class="booking-card"><div class="result-top"><span class="pill">${escapeHTML(b.status)}</span><span>${escapeHTML(b.reference_code)}</span></div><h3>${escapeHTML(b.title)}</h3><p>${escapeHTML(b.provider_name)} · ${escapeHTML(b.service_type)}</p><p>${escapeHTML([b.origin,b.destination,b.location].filter(Boolean).join(' → '))}</p><p>Total: <b>${money(b.total_price,b.currency)}</b> · Quantity: ${Number(b.quantity)}</p><p>Travel date: ${escapeHTML(b.travel_date||b.check_in_date||'Not specified')}</p>${b.status==='pending'?`<button class="secondary" data-cancel="${Number(b.id)}">Cancel pending booking</button>`:''}</article>`).join('');
  }catch(e){$('bookingResults').innerHTML=`<div class="empty">${escapeHTML(e.message)}</div>`;}
}
$('loadBookings').addEventListener('click',loadBookings);
document.addEventListener('click',async e=>{
  const btn=e.target.closest('[data-cancel]');if(!btn)return;
  if(!confirm('Cancel this pending booking?'))return;
  try{const r=await api('cancel_booking',{booking_id:Number(btn.dataset.cancel)});alert(r.message);loadBookings();searchServices();}
  catch(err){alert(err.message);}
});
$('reminderForm').addEventListener('submit',async e=>{
  e.preventDefault();const data=formObject(e.currentTarget);
  try{const r=await api('reminders',data);alert(r.message);e.currentTarget.reset();loadReminders();}
  catch(err){alert(err.message);}
});
async function loadReminders(){
  $('reminderResults').innerHTML='<p class="muted">Loading reminders…</p>';
  try{
    const r=await api('reminders',{},'GET');
    if(!r.data.length){$('reminderResults').innerHTML='<div class="empty">No reminders saved yet.</div>';return;}
    $('reminderResults').innerHTML=r.data.map(x=>`<article class="reminder-card"><span class="pill">${escapeHTML(x.status)}</span><h3>${escapeHTML(x.title)}</h3><p>🕒 ${escapeHTML(x.reminder_at)}</p><p>${escapeHTML(x.notes||'')}</p></article>`).join('');
  }catch(e){$('reminderResults').innerHTML=`<div class="empty">${escapeHTML(e.message)}</div>`;}
}
$('loadReminders').addEventListener('click',loadReminders);

document.addEventListener("DOMContentLoaded", function () {
    const startDate = document.getElementById("start_date");
    const endDate = document.getElementById("end_date");

    const startError = document.getElementById("start_date_error");
    const endError = document.getElementById("end_date_error");

    const notification = document.getElementById("date_notification");

    if (!startDate || !endDate) {
        return;
    }

    // Use today's date in the user's local timezone.
    function getToday() {
        const now = new Date();

        const year = now.getFullYear();
        const month = String(now.getMonth() + 1).padStart(2, "0");
        const day = String(now.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    }

    const today = getToday();

    startDate.min = today;
    endDate.min = today;

    function showNotification(message, isError = true) {
        notification.textContent = message;
        notification.className = isError
            ? "notification notification-error"
            : "notification notification-success";
    }

    function validateDates() {
        let valid = true;

        startError.textContent = "";
        endError.textContent = "";
        notification.textContent = "";

        const start = startDate.value;
        const end = endDate.value;

        if (start && start < today) {
            startError.textContent =
                "This date has already passed. Please choose today or a future date.";

            showNotification(
                "Your start or departure date is in the past."
            );

            valid = false;
        }

        if (end && end < today) {
            endError.textContent =
                "This date has already passed. Please choose today or a future date.";

            showNotification(
                "Your end or return date is in the past."
            );

            valid = false;
        }

        if (start && end && end < start) {
            endError.textContent =
                "Your return date cannot be before your departure date.";

            showNotification(
                "Please choose a return date that is on or after your departure date."
            );

            valid = false;
        }

        return valid;
    }

    startDate.addEventListener("change", function () {
        endDate.min = startDate.value || today;
        validateDates();
    });

    endDate.addEventListener("change", validateDates);

    const searchForm = startDate.closest("form");

    if (searchForm) {
        searchForm.addEventListener("submit", function (event) {
            if (!validateDates()) {
                event.preventDefault();
            }
        });
    }
});