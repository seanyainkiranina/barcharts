<?php
class QuotesModel
{
    private $db;
    /**
     * Instances Object and connection to database
     *
     * @param string $dbs
     * @param string $dbname
     * @param string $uname
     * @param string $password
     */
    function __construct($dbs, $dbname, $uname, $password)
    {
        
        $this->db=new DB\SQL(
            'mysql:host='.$dbs .';port=3306;dbname='.$dbname,
            $uname,
            $password
            );
        
        $checkIfTableExists=$this->db->exec(
            "SHOW TABLES LIKE 'watches';");
        if (count($checkIfTableExists)<1){
            $createTable="CREATE TABLE IF NOT EXISTS `watches` (
                `id` int(11) NOT NULL,
                `watchlist` text,
                PRIMARY KEY (`id`)
              ) ENGINE=MyISAM DEFAULT CHARSET=latin1;";

        $this->db->exec(
            $createTable);



        }    


    }

    /**
     * getWatchList
     * Get Watchlist of symbols from database based up id of user.
     *
     * @param integer $id
     * @return array symbolData
     */
    function getWatchList($id=1)
    {
        $storedList=$this->db->exec(
            'SELECT * from watches where id=?', $id);

        if (isset($storedList[0]['watchlist']) && $storedList[0]['watchlist'] !=null){

            return json_decode($storedList[0]['watchlist']);
        }

        $symbolData = array();

        $allSymbols=$this->db->exec(
                    'SELECT *  from quotes order by symbol DESC');


        foreach ($allSymbols as $aSymbol){

            $symbolData[]=$aSymbol['symbol'];
        }



        return $symbolData;

    }
    
    /**
     * syncWatchList
     * Session variables to DB of the synbols array
     *
     * @param string $watchList
     * @return void
     */
    function syncWatchList($watchList)
    {
        $watchList=json_encode($watchList);
        $this->db->exec('INSERT INTO watches (id, watchlist) VALUES(1, ?) ON DUPLICATE KEY UPDATE    
        watchlist=?',array($watchList,$watchList));


    }

    /**
     * listQuotes
     * Return quotes from array of symbols.
     *
     * @param string $arraySymbols
     * @return array quotes from db
     */
    function listQuotes($arraySymbols)
    {
        array_push($arraySymbols, "PADDING");
        array_walk($arraySymbols, function (&$value, $key) {
            $value=$this->db->quote($value);
        });
        
        $qMarks =  implode(",", $arraySymbols);
        
        return $this->db->exec(
            'SELECT *  from quotes where symbol in (' . $qMarks . ') order by symbol DESC');
    }

    /**
     * searchQuotes
     * Search quotes from the database from symnol passed
     *
     * @param string $sym
     * @return array quotes
     */
    function searchQuotes($sym)
    {
        return $this->db->exec(
            'SELECT * from quotes where symbol=?', $sym);
    }
}
