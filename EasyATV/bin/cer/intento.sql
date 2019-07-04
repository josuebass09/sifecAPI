select `easypos`.`producto`.`idproducto` AS `idproducto`,`easypos`.`producto`.`codigo_interno` AS `codigo_interno`,`easypos`.`producto`.`codigo_barra` AS `codigo_barra`,`easypos`.`producto`.`nombre_producto` AS `nombre_producto`,`easypos`.`producto`.`precio_compra` AS `precio_compra`,`easypos`.`producto`.`precio_venta` AS `precio_venta`,`easypos`.`producto`.`precio_venta_mayoreo` AS `precio_venta_mayoreo`,`easypos`.`producto`.`stock` AS `stock`,`easypos`.`producto`.`stock_min` AS `stock_min`,`easypos`.`producto`.`idcategoria` AS `idcategoria`,`easypos`.`categoria`.`nombre_categoria`,`easypos`.`producto`.`idmarca` AS `idmarca`,`easypos`.`marca`.`nombre_marca` AS `nombre_marca`,`easypos`.`producto`.`idpresentacion` AS `idpresentacion`,`easypos`.`producto`.`estado` AS `estado`,`easypos`.`producto`.`exento` AS `exento`,`easypos`.`producto`.`inventariable` AS `inventariable`,`easypos`.`producto`.`perecedero` AS `perecedero`,`easypos`.`producto`.`unidad_medida_comercial` AS `unid_med_com`,  `easypos`.`detalleimpuestosprod`.`id`,`easypos`.`detalleimpuestosprod`.`id_producto`,`easypos`.`detalleimpuestosprod`.`id_impuesto`,`easypos`.`impuestos`.`descripcion`,`easypos`.`impuestos`.`porcentaje`,`easypos`.`presentacion`.`nombre_presentacion` AS `nombre_presentacion`,`easypos`.`presentacion`.`siglas` AS `siglas` from `easypos`.`producto` left join `easypos`.`detalleimpuestosprod` on `easypos`.`detalleimpuestosprod`.`id_impuesto` = `easypos`.`producto`.`idproducto` left JOIN `easypos`.`impuestos` ON `easypos`.`detalleimpuestosprod`.`id_impuesto`= `easypos`.`impuestos`.`id` left join `easypos`.`categoria` on `easypos`.`categoria`.`idcategoria`=`easypos`.`producto`.`idcategoria` left join `easypos`.`marca` on `easypos`.`producto`.`idmarca` = `easypos`.`marca`.`idmarca` left join `easypos`.`presentacion` on `easypos`.`producto`.`idpresentacion` = `easypos`.`presentacion`.`idpresentacion`
