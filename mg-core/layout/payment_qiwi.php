<script type="text/javascript">
var ie = document.all;
var moz = (navigator.userAgent.indexOf("Mozilla") != -1);
var opera = window.opera;
var brodilka = "";
if(ie && !opera){brodilka = "ie";}
else if(moz){brodilka = "moz";}
else if(opera){brodilka = "opera";}
var inputMasks = new Array();

function kdown(inpt, ev){
    var id = inpt.getAttribute("id");
    var idS = id.substring(0, id.length - 1);
    var idN = Number(id.substring(id.length - 1));
    inputMasks[idS].BlKPress(idN, inpt, ev);
}

function kup(inpt, ck){
    if(Number(inpt.getAttribute("size")) == inpt.value.length){
        var id = inpt.getAttribute("id");
        var idS = id.substring(0, id.length - 1);
        var idN = Number((id.substring(id.length - 1))) + 1;
        var t = document.getElementById(idS + idN);
        if(ck!=8 && ck!=9){
            if(t){t.focus();}
        } else if (ck==8) {
            inpt.value = inpt.value.substring(0, inpt.value.length - 1);
        }
    }
}

function Mask(fieldObj){
    var template = "(\\d{3})\\d{3}-\\d{2}-\\d{2}";
    var parts = [];
    var blocks = [];
    var order = [];
    var value = "";

    var Block = function(pattern){
        var inptsize = Number(pattern.substring(3, pattern.indexOf('}')));
        var idS = fieldObj.getAttribute("id");
        var idN = blocks.length;
        var text = "";

        var checkKey = function(ck){
            return ((ck >= 48) && (ck <= 57)) || ((ck >= 96) && (ck <= 105)) || (ck == 27) || (ck == 8) || (ck == 9) || (ck == 13) || (ck == 45) || (ck == 46) || (ck == 144) || ((ck >= 33) && (ck <= 40)) || ((ck >= 16) && (ck <= 18)) || ((ck >= 112) && (ck <= 123));
        }

        this.makeInput = function(){
            return "<input type='text' " + "size='" + inptsize + "' maxlength='" + inptsize + "'"  + " id='" + idS + idN + "' onKeyDown='kdown(this, event)' onKeyUp='kup(this, event.keyCode)' value='" + text + "'>";
        }

        this.key = function(inpt, ev){
            if(opera) return;
            if(!checkKey(ev.keyCode)){
                switch(brodilka){
                    case "ie":
                        ev.cancelBubble = true;
                        ev.returnValue = false;
                    break;
                    case "moz":
                        ev.preventDefault();
                        ev.stopPropagation();
                    break;
                    case "opera":
                    break;
                    default:
                }
                return;
            }

            if(ev.keyCode == 8 && inpt.value == ""){
                var tid = inpt.getAttribute("id");
                var tidS = tid.substring(0, tid.length - 1);
                var tidN = Number(tid.substring(tid.length - 1)) - 1;
                var t = document.getElementById(tidS + tidN);
                if(t != null) t.focus();
            }
        }

        this.getText = function(){
            text = document.getElementById(idS + idN).value;
            return text;
        }

        this.setText = function(val){
            text = val;
        }

        this.getSize = function() {
            return inptsize;
        }
    }

    this.drawInputs = function(){
        var inputStr = "<span class='Field'>";
        var p = 0;
        var b = 0;
        for (var i = 0; i < order.length; i++) {
            if (order[i] == "p") {
                inputStr += parts[p];
                p++;
            } else {
                inputStr += blocks[b].makeInput();
                b++;
            }
        }
        inputStr += "</span>";
        document.getElementById("div_" + fieldObj.getAttribute("id")).innerHTML = inputStr;
        fieldObj.style.display = "none";
    }

    this.buildFromFields = function() {// constructor
        var tmpstr = template;
        while(tmpstr.indexOf("\\") != -1){
            var slash = tmpstr.indexOf("\\");
            var d = "";
            if(tmpstr.substring(0, slash) != ""){
                parts[parts.length] = tmpstr.substring(0, slash);
                order[order.length] = 'p';
                tmpstr = tmpstr.substring(slash);
            }
            var q = tmpstr.indexOf('}');
            blocks[blocks.length] = new Block(tmpstr.substring(0, q + 1), d);
            tmpstr = tmpstr.substring(q + 1);
            order[order.length] = 'b';
        }
        if (tmpstr != "") {
            parts[parts.length] = tmpstr;
            order[order.length] = 'p';
        }
        this.drawInputs();
    }

    this.buildFromFields();

    this.BlKPress = function(idN, inpt, ev){
        blocks[idN].key(inpt, ev);
    }

    this.makeHInput = function(){
        var name = fieldObj.getAttribute("name");
        document.getElementById("div_" + fieldObj.getAttribute("id")).innerHTML =
            "<input type='text' readonly='readonly' name='" + name + "' value='" + this.getValue() + "'>";
    }

    this.getFName = function(){
        return fieldObj.getAttribute("name");
    }

    this.getValue = function(){
        value = "";
        var p = 0;
        var b = 0;
        for(var i = 0; i < order.length; i++){
            /*if(order[i] == 'p'){
                value += parts[p];
                p++;
            } else {
                value += blocks[b].getText();
                b++;
            }
            */
        	if (order[i] != 'p') {
        		value += blocks[b].getText();
        		b++;
        	}
        }
        return value;
    }

    this.check = function(){
        for(var i in blocks){
            if (blocks[i].getText().length == 0) return false;
        }
        return true;
    }
}
</script>
 <p>
 <br/>
 <br/><em>
 <?php echo lang('paymentDiff1'); ?>"<a href="<?php echo SITE?>/personal"><?php echo lang('paymentDiff2'); ?></a>".
</em>
 <div style="margin:20px auto; padding:5px; width:600px; border:1px solid #ddd; background:#fff; border-radius: 7px; -webkit-border-radius: 7px; -moz-border-radius: 7px; font:normal 14px/14px Geneva,Verdana,Arial,Helvetica,Tahoma,sans-serif;">
	<form action="http://w.qiwi.ru/setInetBill.do" method="get" accept-charset="windows-1251" onSubmit="return checkSubmit();">
	
		<input type="hidden" name="from" value="<?php echo $data['paramArray'][0]['value'] ?>"/>
		<input type="hidden" name="lifetime" value="<?php echo round($data['summ']) ?>"/>
		<input type="hidden" name="check_agt" value="true"/>
		<input type="hidden" name="amount_rub" value="<?php echo round($data['summ']) ?>"/>
		<input type="hidden" name="com" value="Oplata zakaza <?php echo $data['orderNumber'] ?>"/>
    <input type="hidden" name="txn_id" value="<?php echo $data['id'] ?>"/>
    	
		<p style="text-align:center; color:#006699; padding:20px 0px; background:url(https://ishop.qiwi.ru/img/button/logo_31x50.jpg) no-repeat 10px 50%;"><?php echo lang('paymentCreateInvoice'); ?></p>
		<table style="border-collapse:collapse;">
			<tr style="background:#f1f5fa;">
				<td style="color:#a3b52d; width:45%; text-align:center; padding:10px 0px;"><?php echo lang('paymentPhoneExample'); ?></td>
				<td style="padding:10px">
					<input type="text" name="to" id="idto" style="width:130px; border: 1px inset #555;"></input>
					<span id="div_idto"></span>
					<script type="text/javascript">
						inputMasks["idto"] = new Mask(document.getElementById("idto"));
						function checkSubmit() {
							if (inputMasks["idto"].getValue().match(/^\d{10}$/)) {
								document.getElementById("idto").setAttribute("disabled", "disabled");
								inputMasks["idto"].makeHInput();
								return true;
							} else {
								alert(<?php echo lang('paymentPhoneAlert'); ?>);
								return false;
							}
						}
					</script>
    			</td>
			</tr>
		</table>
		<p style="text-align:center;"><input type="submit" value="<?php echo lang('paymentCreateInvoice'); ?>" style=" padding:10px 0;border:none; background:url(https://ishop.qiwi.ru/img/button/superBtBlue.jpg) no-repeat 0 50%; color:#fff; width:300px;"/></p>
	</form>
</div>