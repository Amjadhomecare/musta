function initCustomerSelect (preId = null, preName = '' , selectId ) {
  const $sel = $(`#${selectId}`)

  if ($sel.hasClass('select2-hidden-accessible')) $sel.select2('destroy')

  $sel.select2({
    dropdownParent : $sel.parent(),
    placeholder    : 'Select customer',
    allowClear     : true,
    width          : '100%',
    ajax: {
      url      : '/all-customers',
      delay    : 250,
      dataType : 'json',
      data: params => ({
        search  : params.term,
        context : 'direct-debit',
        page    : params.page || 1
      }),
      processResults: ({ items }) => ({
        results: items.map(i => ({
          id     : i.id,     
          text   : i.text,   
          erp_id : i.erp_id
        }))
      }),
      cache: true
    },
    templateResult    : r => r.loading ? r.text : r.text,
    templateSelection : r => r.text || r.id,
    escapeMarkup      : m => m
  })
  .on('select2:select', e => { form.value.customer_id = e.params.data.erp_id })
  .on('select2:clear',  () => { form.value.customer_id = null })

  if (preId && preName) {
    const opt = new Option(preName, preName, true, true)
    $(opt).data('data', { id: preName, text: preName, erp_id: preId })
    $sel.append(opt).trigger('change')
  }
}


export {  initCustomerSelect }