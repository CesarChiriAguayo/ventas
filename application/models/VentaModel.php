<?php
class VentaModel extends CI_model{
    public function __construct(){
        $this->load->database();
    }

    public function porId($id){
        $venta = new StdClass();
        $venta->detalles = $this->detalleDeVenta($id);
        $venta->productos = $this->productosVendidosDeUnaVenta($id);
        return $venta;
    }

    private function detalleDeVenta($id){
        return $this->db
        ->select("ventas.id, ventas.fecha, sum(productos_vendidos.cantidad * productos_vendidos.precio) as total")
        ->from("ventas")
        ->join("productos_vendidos", "productos_vendidos.id_venta = ventas.id")
        ->where("productos_vendidos.id_venta", $id)
        ->group_by("ventas.id")
        ->get()
        ->row();
    }

    private function productosVendidosDeUnaVenta($idVenta){
        return $this->db
        ->select("productos.descripcion, productos.codigo, productos_vendidos.precio, productos_vendidos.cantidad")
        ->from("productos")
        ->join("productos_vendidos", "productos_vendidos.id_producto = productos.id")
        ->where("productos_vendidos.id_venta", $idVenta)
        ->get()
        ->result();
    }

    public function todas(){
        return $this->db
        ->select("ventas.id, ventas.fecha, sum(productos_vendidos.cantidad * productos_vendidos.precio) as total")
        ->from("ventas")
        ->join("productos_vendidos", "productos_vendidos.id_venta = ventas.id")
        ->group_by("ventas.id")
        ->get()
        ->result();
    }

    public function eliminar($id){
        return $this->db->delete("ventas", array("id" => $id));
    }

    public function nueva($productosVendidos){
        $detalleDeVenta = array("fecha" => date("Y-m-d H:i:s"));
        $this->db->insert("ventas", $detalleDeVenta);

        $idVenta = $this->db->insert_id();
        foreach($productosVendidos as $producto){
            $detalleDeProductoVendido = array(
                "id_producto" => $producto->id,
                "cantidad" => $producto->cantidad,
                "precio" => $producto->precioVenta,
                "id_venta" => $idVenta,
            );
            $this->db->insert("productos_vendidos", $detalleDeProductoVendido);
        }
        return true;
    }
}
?>