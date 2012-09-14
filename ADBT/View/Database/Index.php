<?php

class ADBT_View_Database_Index extends ADBT_View_Database_Base
{

    /** @var ADBT_Model_Table */
    public $table;
    public $title = "Database";

    public function __construct()
    {
        parent::__construct();
    }

    public function outputContent()
    {
        parent::outputContent();
        if ($this->table):
        ?>
<form action="<?php echo $this->url('database/index/'.$this->table->getName()) ?>" method="get">
            <table>
                <caption><p>Find records where&hellip;</p></caption>
                <?php for ($f = 0; $f < count($this->filters); $f++): $filter = $this->filters[$f] ?>
                    <tr>
                        <td><?php if ($f > 0) echo '&hellip;and' ?></td>
                        <td>
                            <select name="filters[<?php echo $f ?>][column]">

                            </select>
                            <?php echo Form::select("filters[$f][column]", $columns, $filter['column']) ?></td>
                        <td><?php echo Form::select("filters[$f][operator]", $operators, $filter['operator']) ?></td>
                        <td colspan="2">
                            <input type="text" name="filters[<?php echo $f ?>][value]" value="<?php echo $filter['value'] ?>" />
                        </td>
                    </tr>
                <?php endfor ?>
                <tr class="submit">
                    <th colspan="3"></th>
                    <th><input type="submit" value="Search" /></th>
                    <th>
                        <?php if (count($this->filters) > 1) echo HTML::anchor(Request::current()->uri() . URL::query(array('filters' => '')), 'Clear Filters') ?>
                    </th>
                </tr>
            </table>
        </form>
        <?php
        endif;
        $this->tableView = new ADBT_View_Database_Table();
        $this->tableView->table = $this->table;
        $this->tableView->output();
    }

}