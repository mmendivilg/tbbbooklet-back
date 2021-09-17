<div style="width:100%; float:left; font-family:sans-serif">
    <div style="float:left; width:74%;">
        <div style="padding-top:-30px; padding-bottom:5px; width:100%">
            <div style="width:25%; float:left;">
                <img src=<?= $gasto->empresa->logo ?> alt="Logo" style="margin-top:0px;">
            </div>
            <div style="width:50%; text-align:center; float:left; ">
                <p style="margin:0; font-size:26px; color: #337ab7;">Empresa_Nombre</p>
                <h4 style="margin-top:10;">Summary of Pre-Expense #<?= ($gasto->id) ?></h4>
                
            </div>
        </div>
    </div>
</div>
<div style="width:100%; float:left; font-family:sans-serif">
  
    <table id="hor-zebra">
        <tr>
            <td style="font-weight: bold;">printdate</td>
            <td><?= $gasto->fecha_pago ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">amount</td>
            <td><?= $gasto->monto ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">currency</td>
            <td><?= $gasto->moneda->abreviacion ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">beneficiario</td>
            <td><?= $gasto->beneficiario ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">concept</td>
            <td><?= $gasto->concepto ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">amountmxn</td>
            <td><?= $gasto->monto ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">amountusd</td>
            <td><?= $gasto->monto ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">bankaccount</td>
            <td><?= $gasto->cuentaBancaria->abreviacion ?></td>
        </tr>
        <tr>
            <td style="font-weight: bold;">budgetconcept</td>
            <td><?= '$gasto->budgetconcept' ?></td>
        </tr>
     
        <tr>
            <td style="font-weight: bold;">comments</td>
            <td><?= $gasto->descripcion ?></td>
        </tr>        
    </table> 
</div>
<?php  if($gasto->esGastoMultiple()) {?>
<h3>Details</h3>
    <table id="hor-zebra">
        <tr>
            <th>#</th>
            <th>CFDI</th>
            <th>Concept</th>
            <th>Amount</th>
            <th>Budget Category</th>
            <th>Project</th>
        </tr>       
        <?php //Declare variables for reading fees statements and payment details
        $total  = 0;
        foreach($gasto->gastosMenores as $i => $menor)
        {
            $total  += $menor->monto;
            ?>
        <tr>
            <td><?= $i + 1 ?></td>
            <td><?= '$menor->cfdiuuid_short' ?></td>
            <?php if($menor->factura) {?>
                <td><?= $menor->factura->concepto ?></td>
            <?php } else {?>
                <td></td>
            <?php }?>
            <td><?= $menor->monto ?></td>
            <td><?= '$menor->budgetconcept' ?></td>
            <td><?= '$menor->proyectos' ?></td>
        </tr>
        <?php }; ?>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th class = "nowrap"><?= $total ?></th>
            <th></th>
            <th></th>
        </tr>  
    </table>
<?php  } ?>