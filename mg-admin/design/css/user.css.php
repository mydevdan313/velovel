<?php
	$scheme = unserialize(stripslashes(MG::getSetting('interface')));
?>

<style type="text/css">
	/* основная цветовая схема */
	.mg-admin-html table.main-table tr.selected td:first-child { 
		border-left-color: <?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .checkbox label:after {
		border-left:2px solid <?php echo $scheme['colorMain']; ?>;
		border-bottom:2px solid <?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .checkbox input[type=checkbox]:checked+label,
	.checkbox input[type=radio]:checked+label,
	.radio input[type=checkbox]:checked+label,
	.radio input[type=radio]:checked+label, {
		border-color:<?php echo $scheme['colorMain']; ?>!important;
	}
	.mg-admin-html .radio label:after,
	.mg-admin-html .jquery-ui-sorter-placeholder {
		background:<?php echo $scheme['colorMain']; ?>!important;
	}
	.mg-admin-html .table-pagination .pagination li.current a {
		color:#fff;background:<?php echo $scheme['colorMain']; ?>;
		border-color:<?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .modal .fa {
		color:<?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .ui-slider .ui-slider-range {
		background:<?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .tabs.custom-tabs {
		border:1px solid <?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .tabs.custom-tabs li a {
		color:<?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .tabs.custom-tabs li.is-active a {
		background:<?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .wrapper .header .header-top {
		background:<?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .wrapper .header .header-nav .top-menu .nav-list>li>a:before {
		background:<?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .section-settings .file-template.editing-file,
	.mg-admin-html .section-settings #customAdminLogo {
		background-color:<?php echo $scheme['colorMain']; ?>;
	}
	.mg-admin-html .button.primary,
	.mg-admin-html .button.primary:focus,
	.mg-admin-html .button.primary:hover,
	.mg-admin-html .button,
	.mg-admin-html .button:focus,
	.mg-admin-html .button:hover,
	.admin-top-menu,
	.mg-admin-html .sk-folding-cube .sk-cube:before {
		background:<?php echo $scheme['colorMain']; ?>;
	}

	.mg-admin-html .group-row.show {
	    background: <?php echo $scheme['colorMain']; ?> !important;
	}
	.mg-admin-html .variant-row .left-line,
	.mg-admin-html .variant-row .hor-line {
		border-color: <?php echo $scheme['colorMain']; ?> !important;
	}

	/* цвета ссылок */
	.mg-admin-html .link,
	.mg-admin-html a {
		color: <?php echo $scheme['colorLink']; ?>;
	}
	.mg-admin-html a.link {
		border-bottom: 1px dashed <?php echo $scheme['colorLink']; ?>;
	}

	/* кнопка сохранения */
	.mg-admin-html .button.success,
	.mg-admin-html .button.success:focus,
	.mg-admin-html .button.success:hover {
		background-color: <?php echo $scheme['colorSave']; ?>;
	}

	/* рамки */
	.mg-admin-html .widget.add-order .widget-footer, 
	.mg-admin-html .widget.settings .widget-footer, 
	.mg-admin-html .widget.table .widget-footer,
	.mg-admin-html .widget-panel,
	.mg-admin-html .main-table td,
	.mg-admin-html .checkbox label,
	.mg-admin-html select,
	.mg-admin-html .linkPage,
	.mg-admin-html input,
	.mg-admin-html textarea,
	.mg-admin-html .reveal-header,
	.mg-admin-html .reveal-footer,
	.mg-admin-html label,
	.mg-admin-html .accordion-item,
	.mg-admin-html .price-settings,
	.mg-admin-html .price-footer,
	.mg-admin-html .color,
	.mg-admin-html .size-map th,
	.mg-admin-html .size-map td,
	.mg-admin-html .border-color,
	.integration-container .sideBorder,
	.mg-admin-html .filter-form .range-field .input-range,
	.mg-admin-html .border-top {
		border-color: <?php echo $scheme['colorBorder']; ?> !important;
	}
	.mg-admin-html .main-table td {
		border-left: 0 !important;
	}

	/* прочие кнопки */
	.mg-admin-html .button.secondary,
	.mg-admin-html .button.secondary:focus,
	.mg-admin-html .button.secondary:hover {
		background-color: <?php echo $scheme['colorSecondary']; ?>;
	}
</style>