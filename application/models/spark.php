<?php

class Spark extends CI_Model
{
    public static function insert($data)
    {
        $CI = &get_instance();
        $data['modified'] = date('Y-m-d H:i:s');
        $data['created']  = date('Y-m-d H:i:s');
        return $CI->db->insert('sparks', $data);
    }

    /**
     * Get a spark object by its name and version
     * @param string $name
     * @param string $version
     * @return Spark
     */
    public static function get($name, $version)
    {
        if($version == 'HEAD')
            return self::getLatest ($name);

        $CI = &get_instance();
        $CI->db->select("s.*, v.version, v.is_deactivated");
        $CI->db->from('sparks s');
        $CI->db->join('versions v', 'v.spark_id = s.id');
        $CI->db->where('s.name', $name);
        $CI->db->where('v.version', $version);

        return $CI->db->get()->row(0, 'Spark');
    }

    /**
     * Get spark info by its name and version
     * @param string $name
     * @param string $version
     * @return Spark
     */
    public static function getInfo($name)
    {       
        $CI = &get_instance();
        $CI->db->select("s.*");
        $CI->db->from('sparks s');
        $CI->db->where('s.name', $name);

        return $CI->db->get()->row(0, 'Spark');
    }

    /**
     * Get a spark by its id
     * @param int $id
     * @return Spark
     */
    public static function getById($id)
    {
        $CI = &get_instance();
        $CI->db->select("s.*");
        $CI->db->from('sparks s');
        $CI->db->where('s.id', $id);

        return $CI->db->get()->row(0, 'Spark');
    }

    /**
     * Get the latest version
     * @param string $name
     * @return Spark
     */
    public static function getLatest($name)
    {
        $CI = &get_instance();
        $CI->db->select("s.*, v.version");
        $CI->db->from('sparks s');
        $CI->db->join('versions v', 'v.spark_id = s.id');
        $CI->db->where('s.name', $name);
        $CI->db->order_by('v.created', 'DESC');
        $CI->db->limit(1);

        return $CI->db->get()->row(0, 'Spark');
    }

    /**
     * Get the top sparks.. however that's done
     * @param int $n
     * @return array[Spark]
     */
    public static function getTop($n = 10)
    {
        $CI = &get_instance();
        $CI->db->select("s.*, c.username");
        $CI->db->from('sparks s');
        $CI->db->join('contributors c', 's.contributor_id = c.id');
        $CI->db->order_by('s.created', 'DESC');
        $CI->db->limit($n);

        return $CI->db->get()->result('Spark');
    }

    /**
     * The the contributor object for a spark
     * @return Contributor
     */
    public function getContributor()
    {
        $this->load->model('contributor');
        return $this->db->get_where('contributors', array('id' => $this->contributor_id))->row(0, 'Contributor');
    }

    /**
     * Get this spark's version list
     * @return array[Version]
     */
    public function getVersions()
    {
        $this->load->model('version');
        $this->db->order_by('created', 'DESC');
        return $this->db->get_where('versions', array('spark_id' => $this->id))->result('Version');
    }

    public function recordInstall()
    {
        return $this->db->insert('installs', array('spark_id' => $this->id, 'created' => date('Y-m-d H:i:s')));
    }

    public function setVersionStatus($version, $deactivated = FALSE)
    {
        $this->db->where('spark_id', $this->id);
        $this->db->where('version', $version);

        $this->db->update('versions', array('is_deactivated' => $deactivated));
    }
}