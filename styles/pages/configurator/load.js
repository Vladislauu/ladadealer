const configurator_Car_Family = document.getElementById("configurator-car-family")
const configurator_Car_Version = document.getElementById('configurator-car-version')
const urlParams = new URLSearchParams(window.location.search)
const family = urlParams.get('car')
const version = urlParams.get('version')
if (family) 
{
    let option = configurator_Car_Family.querySelector(`option[value="${family}"]`)
    if (option) option.selected = true;
    else alert("На странице произошла ошибка")
}

if (version) 
{
    let option = configurator_Car_Version.querySelector(`option[value="${version}"]`)
    if (option) option.selected = true;
    else alert("На странице произошла ошибка")
}