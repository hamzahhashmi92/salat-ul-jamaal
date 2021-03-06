<?php 
/**
* 
*/
class Reservation_model extends CI_model
{
	
	function __construct()
	{
		
	}



    function count()
    {
        $data["total"]=$this->db->count_all('reservations');
        $this->db->where("status",0);
        $this->db->from('reservations');
        $data["pending"]   = $this->db->count_all_results();
        $data["completed"] =  $data["total"] - $data["pending"];
        return $data;
    }

    function update($id,$data)
    {
         // var_dump($data);
         // exit();
        $this->db->trans_start();
        $update_data = array(
                        'id'    => $id,
                        'customer_id'    => $data["customer_id"],
                        'date'  => date('Y-m-d', strtotime($data['date'])),
                        'time'  => date('H:i:s',strtotime($data['time'])),
                        'notes'  => $data['notes']
                        );

        $this->db->replace('reservations', $update_data);
        $status=$this->db->trans_complete();
        return $status;
    }

    function completed($id)
    {
        $this->db->trans_start();
        $this->db->set("status",1);
        $this->db->where("id",$id);
        $this->db->update("reservations");
        $status=$this->db->trans_complete();
        return $status;
    }


	function submit_reservation($data)
	 {
        // echo "<pre>";
        // var_dump($data);
        // echo "<pre>";
        $this->db->trans_start();
		$this->db->insert('customer',array( 'name'  => $data['reservation-name'],
											'email' => $data['reservation-email'],
											'phone' => $data['reservation-phone']
                                            
                                            ));
		$customer_id=$this->db->insert_id();

		$this->db->insert('reservations',array(
                                            'customer_id' => $customer_id,
											'date'        => date('Y-m-d', strtotime($data['reservation-date'])),
											'time'        => date('H:i:s',strtotime($data['reservation-time'])),
                                            'notes'       => $data['reservation-note'],
                                            'status'      => 0
                                            ));
		$reservation_id=$this->db->insert_id();

		$this->db->insert('cosmetics',array('reservation_id'   => $reservation_id,
											'facial'           => isset($data['facial'])?$data['facial']:'0',
											'eyebrow'          => isset($data['eyebrow'])?$data['eyebrow']:'0',
											'microdermabrasion'=> isset($data['microdermabrasion'])?$data['microdermabrasion']:0,
											'acne_treatment'   => isset($data['acne_treatment'])?$data['acne_treatment']:'0'
											));
        $this->db->insert('hairdressing',array(
                                            'reservation_id' => $reservation_id,
                                            'wash'           => isset($data['wash'])?$data['wash']:'0',
                                            'cut_finish'     => isset($data['cut_finish'])?$data['cut_finish']:'0',
                                            'blow_dries'     => isset($data['blow_dries'])?$data['blow_dries']:'0',
                                            'hair_colouring' => isset($data['hair_colouring'])?$data['hair_colouring']:'0'
                                            ));
        
        
        $this->db->trans_complete();
        
        if($this->db->trans_status())
        {
            return true;
        }
        
        else
            return false;
	}
    public function get_reservations()
    {
        $this->db->select('reservations.id,customer.name as customer,date,time,notes,status');
        $this->db->from('reservations');
        $this->db->join('customer', 'customer.id = reservations.customer_id');
        $query=$this->db->get_where('',array('status'=>0));
        return $query->result_array();

    }
       public function get_products()
    {
        $this->db->select('*');
        $this->db->from('products');
        $query=$this->db->get();
        return $query->result_array();

    }
    
      public function product_detail($id)
    {
        $this->db->select('*');
        $query = $this->db->get_where('products', array('product_id' => $id));
        return $query->row_array();

    }
    
    public function get_details($id)
    {
        $this->db->select('reservations.notes as notes,reservations.date,reservations.time,reservations.id as reservation_id,reservations.customer_id,c.name as name,c.email as email,c.phone as phone,cosmetics.facial as facial,cosmetics.eyebrow as eyebrow,cosmetics.microdermabrasion as micro,cosmetics.acne_treatment as acne,hd.wash as wash,hd.cut_finish as cut_finish,hd.blow_dries as blow,hd.hair_colouring as hairclr');
        $this->db->from('reservations');
        $this->db->where('reservations.id',$id);
        $this->db->where('reservations.status',0);
        $this->db->join('customer as c', 'c.id = reservations.customer_id');
        $this->db->join('cosmetics', 'cosmetics.reservation_id = reservations.id');
        $this->db->join('hairdressing as hd', 'hd.reservation_id = reservations.id');
        $query=$this->db->get();
        // var_dump($this->db->last_query());
        return $query->row();
         // return $query->result_array();

    }

    
    function add_product($data)
    {   
        $this->db->trans_start();
        $this->db->insert('products',array('name'=>$data['name'],'price'=>$data['price'],'description'=>$data['description']));
        $this->db->trans_complete();
        
        if($this->db->trans_status())
        {
            return true;
        }
        else
            return false;
    }
    
}


?>
