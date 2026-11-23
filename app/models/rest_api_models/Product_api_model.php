    <?php defined('BASEPATH') OR exit('No direct script access allowed');

    class Product_api_model extends CI_Model
    {

        public function __construct()
        {
            parent::__construct();
            $this->load->config('ion_auth', TRUE);

            $this->hash_method = $this->config->item('hash_method', 'ion_auth');
            $this->store_salt = $this->config->item('store_salt', 'ion_auth');
            $this->salt_length = $this->config->item('salt_length', 'ion_auth');

            //load the bcrypt class if needed
            if ($this->hash_method == 'bcrypt') {
                if ($this->random_rounds) {
                    $rand = rand($this->min_rounds, $this->max_rounds);
                    $rounds = array('rounds' => $rand);
                } else {
                    $rounds = array('rounds' => $this->default_rounds);
                }

                $this->load->library('bcrypt', $rounds);
            }

        }


        public function getProducts($where){
          $this->db->select("".$this->db->dbprefix('products') . ".id,".$this->db->dbprefix('products') . ".code,".$this->db->dbprefix('products') . ".name,".$this->db->dbprefix('products') . ".price,".$this->db->dbprefix('brands') . ".image,width,profile,rim_diameter,".$this->db->dbprefix('products') . ".slug,product_details,".$this->db->dbprefix('brands') . ".name as brandname,".$this->db->dbprefix('tax_rates') .".code as taxcode,".$this->db->dbprefix('product_prices') . ".price as web_price", FALSE)
          ->from('products')
          ->join('brands','products.brand = brands.id','left')
          ->join('tax_rates','products.tax_rate = tax_rates.id','left')
          ->join('product_prices','product_prices.product_id = '.$this->db->dbprefix("products") . '.id','left');

          $this->db->where( 'products.type', 'standard' );
          $q = $this->db->get();
          if ($q->num_rows() > 0) {
              foreach (($q->result()) as $row) {
                 $data[] = $row;
             }
             return $data;

         }else{
            return false;
        }

    }

        //get drivers details by only email id
    public function getVanDriverByEmail($email){
        $result = $this->db->get_where('lw_van_drivers', array('email' => $email), 1);

        if ($result->num_rows() > 0) {
            return $result->row();
        }
        return FALSE;
    }



        //from auth_model
    public function forgotten_passwordUpdateCode($identity)
    {
        if (empty($identity)) {
            return FALSE;
        }

            //All some more randomness
        $activation_code_part = "";
        if (function_exists("openssl_random_pseudo_bytes")) {
            $activation_code_part = openssl_random_pseudo_bytes(128);
        }

        for ($i = 0; $i < 1024; $i++) {
            $activation_code_part = sha1($activation_code_part . mt_rand() . microtime());
        }

        $key = $this->hash_code($activation_code_part . $identity);

        $update = array(
            'forgotten_password_code' => $key,
            'forgotten_password_time' => time()
        );

        $this->db->where('email',$identity);
        $sql=$this->db->update('lw_van_drivers',$update);

        return $sql;
    }

        //hash code  used in forgot password
    public function hash_code($password)
    {
        return $this->hash_password($password, FALSE, TRUE);
    }

        //hash password used in forgot password
    public function hash_password($password, $salt = false, $use_sha1_override = FALSE)
    {
        if (empty($password)) {
            return FALSE;
        }

            //bcrypt
        if ($use_sha1_override === FALSE && $this->hash_method == 'bcrypt') {
            return $this->bcrypt->hash($password);
        }

        if ($this->store_salt && $salt) {
            return sha1($password . $salt);
        } else {
            $salt = $this->salt();
            return $salt . substr(sha1($salt . $password), 0, -$this->salt_length);
        }
    }

        //create salt used in forgot password
    public function salt()
    {
        return substr(md5(uniqid(rand(), true)), 0, $this->salt_length);
    }


    public function getAllCategories() {
        $this->db->select('id,code,name,parent_id');
        $this->db->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSubCategories($parent_id) {
        $this->db->select('id,code,name,parent_id');
        $this->db->where('parent_id', $parent_id)->order_by('name');
        $q = $this->db->get("categories");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductsCountByCategorySubcategory($category_id=NULL,$subcategory_id=NULL)
    {
        $count=0;

        try {
           $q=NULL;
           if(!empty($category_id) && !empty($subcategory_id))
           {
            $q='SELECT COUNT(*) as count FROM  sma_products where category_id='.$category_id.' and '.' subcategory_id='.$subcategory_id;
        }
        else if(empty($subcategory_id))
        {
         $q='SELECT COUNT(*) as count FROM  sma_products where category_id='.$category_id;
     }
     if(!empty($q))
     {
       $cntQuery=$this->db->query($q);
       if($cntQuery->num_rows()>0)
       {
                                //echo "".json_encode($cntQuery->result()[0]);
        $count=$cntQuery->result()[0]->count;     
    }
    }

    } catch (Exception $e) {
        $count=0;

    }
    return $count;
}


}
