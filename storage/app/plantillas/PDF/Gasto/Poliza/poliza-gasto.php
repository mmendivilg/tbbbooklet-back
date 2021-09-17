<div style="width:100%; float:left; font-family:sans-serif">
	<div style="float:left; width:74%; border-radius:10px; border:3px solid black;">
		<div style="padding-top:5px; padding-bottom:5px; width:100%">
		<div style="width:15%; float:left;">
			<?php if( $gasto->empresa->getOriginal('logo') ) { ?>
                <img src='<?= $gasto->empresa->logo ?>' alt="" style="margin-top:15px;">
			<?php } ?>
			</div>
			<div style="width:74%; float:left; border-left:3px solid black; padding-top:10px; padding-bottom:10px;">
				<div style="padding-left:10px">
					<p style="margin:0"><strong><?= $datos['gasto']['poliza'][0] ?></strong></p>
					<p style="margin:0"><?= $datos['gasto']['poliza'][1] ?></p>
					<p style="margin:0"><?= $datos['gasto']['poliza'][2] ?></p>
					<p style="margin:0"><?= $datos['gasto']['poliza'][3] ?></p>
					<p style="margin:0"><?= $datos['gasto']['poliza'][0] ?></p>
				</div>
			</div>
		</div>
	</div>
	<div style="width:25%; float:left;  margin-left:15px">
		<div style="width:100%; text-align:center">
			<h4 style="border:3px solid black; border-radius:10px; padding-top:5px; padding-bottom:5px; font-weight:bold">POLIZA</h4>
		</div>
		<div style="width:100%">
			<div style="width:35%; float:left; padding-top:5px; font-weight:bold">Fecha:</div>
			<div style="width:55%; float:left; border-radius:5px; border: 2px solid black; padding:5px; font-size: 13px;">
			<?php
				echo date("d")." / ".date("m")." / ".date("Y");
				?>
			</div>
		</div>
		<div style="width:100%; float:left;">
				<p style="padding: 40px 0 0px 0px; text-align:center; font-weight:bold">FACTURA</p>
		</div>
	</div>
</div>

<div style="width:100%; float:left; font-family:sans-serif; margin-top:10px">
	<div style="float:left; width:74%;">
		<div style="width:100%">
			<div style="width:22%; float:left">
				<p style="margin:0; padding: 5px 0 5px 0px; text-align:left; font-weight:bold">BENEFICIARIO:</p>
			</div>
			<div style="width:77%; float:left; border-radius:5px; border:1px solid black;">
				<p style="margin:0; padding: 5px 0 5px 0px; text-align:center"><?= $gasto->beneficiario ?></p>
			</div>
		</div>
		<div style="width:100%; margin-top:5px">
			<div style="width:22%; float:left">
				<p style="margin:0; padding: 5px 0 5px 0px; text-align:left; font-weight:bold">CANTIDAD:</p>
			</div>
			<div style="width:77%; float:left; border-radius:5px; border:1px solid black;">
				<!--p style="margin:0; padding: 5px 0 5px 0px; text-align:center">&nbsp;</p-->
				<p style="margin:0; padding: 5px 0 5px 0px; text-align:center"><?= $gasto->montoTexto( 'es' ) ?></p>
			</div>
		</div>
		<div style="width:100%; margin-top:40px;">
			<div style="width:15%; float:left">
				<p style="margin:0; padding: 0px 0 0px 0px; text-align:center; font-weight:bold; font-size: 13px"># Cheque / Ref:</p>
			</div>
			<div style="width:15%; float:left; border-radius:5px; border:1px solid black;">
				<p style="margin:0; padding: 5px 0 5px 0px; text-align:center"><?= $gasto->referencia ?></p>
			</div>
			<div style="width:15%; float:left">
				<p style="margin:0; padding: 5px 0 5px 0px; text-align:center; font-weight:bold"># Cuenta</p>
			</div>
			<div style="width:51%; float:left; border-radius:5px; border:1px solid black;">
				<p style="margin:0; padding: 5px 0 5px 0px; text-align:center">
					<?= $gasto->cuentaBancaria->abreviacion ?>
				</p>
			</div>
	 		<div style="width:99%; margin-top: 15px;">
				<div style="width:22%; float:left">
					<p style="margin:0; padding: 5px 0 5px 0px; text-align:left; font-weight:bold">CONCEPTO:</p>
				</div>
				<div style="width:75%; float:left; border-radius:5px; border:1px solid black;">
					<p style="margin:0; padding: 5px 0 5px 0px; text-align:center"><?= $gasto->concepto ?></p>
				</div>
			</div> 
		</div>
	</div>
	<div style="width:25%; float:left;  margin-left:15px">
		<div style="width:100%; font-size: 8px; text-align: center;;">FOLIO</div>
		<div style="width:100%; float:left; border-radius:5px; border:1px solid black;">
			<p style="margin:0; padding: 5px 0 5px 0px; float:left;">
				<?= $gasto->factura->folio  ?>
			</p>
		</div>

		<?php if($gasto->id_factura){ ?>
			<div style="width:100%; font-size: 8px; text-align: center;;">SUBTOTAL</div>
			<div style="width:100%; float:right; border-radius:5px; border:1px solid black;">
				<div style="margin:0; padding: 5px 0 5px 0px; text-align: center;">
					<?= '$ '.$gasto->factura->subtotal ?>
				</div>
			</div>

			<div style="width:100%; font-size: 8px; text-align: center;;">IVA</div>
		<div style="width:100%; float:left; border-radius:5px; border:1px solid black;">
			<p style="margin:0; padding: 5px 0 5px 0px; text-align:center">
				<?= '$ '.$gasto->factura->iva ?>
			</p>
		</div>
		<?php if($gasto->factura->ret_isr || $gasto->factura->ret_iva) { ?>
			<div style="width:100%; font-size: 8px; text-align: center;;">RET IVA</div>
			<div style="width:100%; float:left; border-radius:5px; border:1px solid black;">
				<p style="margin:0; padding: 5px 0 5px 0px; text-align:center">
					<?= '$ '.$gasto->factura->ret_iva ?>
				</p>
			</div>
			<div style="width:100%; font-size: 8px; text-align: center;;">RET ISR</div>
			<div style="width:100%; float:left; border-radius:5px; border:1px solid black;">
				<p style="margin:0; padding: 5px 0 5px 0px; text-align:center">
					<?= '$ '.$gasto->factura->ret_isr ?>
				</p>
			</div>
		<?php } ?>
		<div style="width:100%; font-size: 8px; text-align: center;;">TOTAL</div>
		<div style="width:100%; float:left; border-radius:5px; border:1px solid black;">
			<p style="margin:0; padding: 5px 0 5px 0px; text-align:center">
				<?= '$ '.$gasto->factura->total ?>
			</p>
		</div>
		<?php } else { ?>
		<div style="width:100%; float:left; border-radius:5px; border:1px solid black; margin-top:5px">
			<p style="margin:0; padding: 5px 0 5px 0px; text-align:center">
				<?= '$ '.$gasto->monto ?>
			</p>
		</div>
		<?php } ?>
		<div style="width:100%; text-align: center; font-weight:bold; margin-top:5px;">RECIBIDO</div>
		<div style="width:100%; float:left; border-radius:5px; border:1px solid black; height:80px; margin-top:5px;">
		</div>
	</div>
</div>

<?php foreach($gasto->gastosMenores as $menor) {?>
<?php
echo $this->render( 'poliza-gasto-menor.php', [
	'gasto' => $menor,
	'datos' => $datos,
] );
?>
<?php } ?>