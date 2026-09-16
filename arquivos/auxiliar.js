var URL_BASE = param;

function pay(){
    document.getElementById("form-checkout-shipment").submit();
}

function openAndClose(selection) {
    if(selection == 'pixSelection'){
        let pixDiv = document.getElementById("pixDiv");
        let pixContent = document.getElementById("pixContent");
        pixDiv.style.cssText ='background:#F4F6F8;border-color:#333;';
        pixContent.style.cssText ='display:block!important;';
    } else {

    }
}

function addQtd(e) {
    let resumoQtd = document.getElementById("resumoQtd")
    let quantityHTML = document.getElementById("quantityHTML")
    let valorTotal = document.getElementById("valorTotal")
    let valorTotal2 = document.getElementById("valorTotal2")
    let valorTotal3 = document.getElementById("valorTotal3")
    let valorTotal4 = document.getElementById("valorTotal4")
    let valorTotal5 = document.getElementById("valorTotal5")
    let descontodivvalue = document.getElementById("descontodivvalue")
    let descontodivvalue2 = document.getElementById("descontodivvalue2")
    // let totalnopix = document.getElementById("totalnopix")

    const minhaUrl = URL_BASE+"/sections/carrinho.php?adicionar=0"

    jQuery.get(minhaUrl, "", function(data){

        var qtd = data.quantidade;
            var subtotal = data.total;
            var subt = data.subt
            var desct = data.desct
            resumoQtd.innerHTML = "("+qtd+")"
            quantityHTML.value = qtd
            valorTotal.innerHTML = "R$ "+subtotal
            valorTotal2.innerHTML = "R$ "+subtotal
            valorTotal3.innerHTML = "R$ "+subtotal
            valorTotal4.innerHTML = "R$ "+subtotal
            valorTotal5.innerHTML = "R$ "+subt
            // totalnopix.innerHTML = "R$ "+subt
            descontodivvalue.innerHTML = "- R$ "+desct
            descontodivvalue2.innerHTML = "- R$ "+desct

    }, "json")
    // fetch(minhaUrl).then(function(response) {
    // }).then(function(data) {

    // }).catch(function(e) {
    //     console.log(e);
    // });

    // async function loadQtd() {

    //     jQuery.get(URL_BASE+'/sections/carrinho.php?getCarrinho', "", function(data){

    //         var qtd = data.quantidade;
    //         var subtotal = data.total;
    //         var subt = data.subt
    //         var desct = data.desct
    //         resumoQtd.innerHTML = "("+qtd+")"
    //         quantityHTML.value = qtd
    //         valorTotal.innerHTML = "R$ "+subtotal
    //         valorTotal2.innerHTML = "R$ "+subtotal
    //         valorTotal3.innerHTML = "R$ "+subtotal
    //         valorTotal4.innerHTML = "R$ "+subtotal
    //         valorTotal5.innerHTML = "R$ "+subt
    //         totalnopix.innerHTML = "R$ "+subt
    //         descontodivvalue.innerHTML = "- R$ "+desct
    //         descontodivvalue2.innerHTML = "- R$ "+desct

    //     },"JSON")


    //     // logs [{ name: 'Joker'}, { name: 'Batman' }]
    // }

    // loadQtd();

}

function removeQtd() {
    let resumoQtd = document.getElementById("resumoQtd")
    let quantityHTML = document.getElementById("quantityHTML")
    let valorTotal = document.getElementById("valorTotal")
    let valorTotal2 = document.getElementById("valorTotal2")
    let valorTotal3 = document.getElementById("valorTotal3")
    let valorTotal4 = document.getElementById("valorTotal4")
    let valorTotal5 = document.getElementById("valorTotal5")
    let totalnopix = document.getElementById("totalnopix")
    let descontodivvalue = document.getElementById("descontodivvalue")
    let descontodivvalue2 = document.getElementById("descontodivvalue2")

    const minhaUrl = URL_BASE+"/sections/carrinho.php?remover=0"

    jQuery.get(minhaUrl, "", function(data){

        var qtd = data.quantidade;
            var subtotal = data.total;
            var subt = data.subt
            var desct = data.desct
            resumoQtd.innerHTML = "("+qtd+")"
            quantityHTML.value = qtd
            valorTotal.innerHTML = "R$ "+subtotal
            valorTotal2.innerHTML = "R$ "+subtotal
            valorTotal3.innerHTML = "R$ "+subtotal
            valorTotal4.innerHTML = "R$ "+subtotal
            valorTotal5.innerHTML = "R$ "+subt
            totalnopix.innerHTML = "R$ "+subt
            descontodivvalue.innerHTML = "- R$ "+desct
            descontodivvalue2.innerHTML = "- R$ "+desct

    }, "json")

    // const minhaUrl = URL_BASE+"/sections/carrinho.php?remover=0"

    // fetch(minhaUrl).then(function(response) {
    // }).then(function(data) {

    // }).catch(function(e) {
    //     console.log(e);
    // });

    // async function loadQtd() {
    //     const response = await fetch(URL_BASE+'/sections/carrinho.php?getCarrinho');
    //     const data = await response.json();
    //     var qtd = data.quantidade;
    //     var subtotal = data.total;
    //     var subt = data.subt
    //     var desct = data.desct
    //     resumoQtd.innerHTML = "("+qtd+")"
    //     quantityHTML.value = qtd
    //     valorTotal.innerHTML = "R$ "+subtotal
    //     valorTotal2.innerHTML = "R$ "+subtotal
    //     valorTotal3.innerHTML = "R$ "+subtotal
    //     valorTotal4.innerHTML = "R$ "+subtotal
    //     valorTotal5.innerHTML = "R$ "+subt
    //     totalnopix.innerHTML = "R$ "+subt
    //     descontodivvalue.innerHTML = "- R$ "+desct
    //     descontodivvalue2.innerHTML = "- R$ "+desct
    //     // logs [{ name: 'Joker'}, { name: 'Batman' }]
    // }

    // loadQtd();
}
