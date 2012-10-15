<?php

class ADBT_View_Database_Table extends ADBT_View_HTML
{

    /** @var ADBT_Model_Table */
    public $table;

    public function output()
    {
        if (!$this->table)
            return;
        ?>

        <?php $rows = $this->table->getRows(true) ?>

        <table>
            <caption>
                Found <?php echo number_format($this->table->count_records()) ?>
                record<?php if ($this->table->count_records() != 1) echo 's' ?>
                <br /><?php $this->outputPagination() ?>
            </caption>
            <thead>
                <tr>

                    <?php if ($this->table->get_pk_column()): ?>
                        <th>&nbsp;</th>
                    <?php endif ?>

                    <?php
                    foreach ($this->table->getColumns() as $column) {
                        $title = $this->titlecase($column->getName());
                        $orderdir = $this->table->getOrderDir();
                        $class = '';
                        if ($this->table->getOrderBy() == $column->getName()) {
                            $title .= "&nbsp;<img src='" . $this->url("resources/img/sort_$orderdir.png") . "' alt='Sort-direction icon' />";
                            $orderdir = ($orderdir == 'desc') ? 'asc' : 'desc';
                            $class = 'sorted';
                        }
                        $params = array('orderby'=>$column->getName(), 'orderdir'=>$orderdir);
                        $url = $this->url("/database/index/".$this->table->getName(), $params);
                        ?>
                        <th class="<?php echo $class ?>">
                            <a href="<?php echo $url ?>"><?php echo $title ?></a>
                        </th>
                    <?php } // foreach ($this->table->getColumns() as $column) ?>

                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row) { ?>
                    <tr>

                        <?php if ($this->table->get_pk_column()): ?>
                            <td>
                                <?php
                                $pk_name = $this->table->get_pk_column()->getName();
                                $label = ($this->table->can('update')) ? 'Edit' : 'View';
                                $url = 'database/edit/' . $this->table->getName() . '/' . $row[$pk_name];
                                ?>
                                <a href="<?php echo $this->url($url) ?>">
                                    <?php echo $label ?>
                                </a>
                            </td>
                        <?php endif // if ($the_table->get_pk_column())  ?>

                        <?php foreach ($this->table->getColumns() as $column) { ?>
                            <td class="<?php echo $column->get_type() ?>-type <?php if ($column->is_foreign_key()) echo 'foreign-key' ?>">
                                <?php
                                //$form_field_name = 'data[' . $this->row[$pk_name] . '][' . $column->getName() . ']'
                                $field = new ADBT_View_Database_Field($column, $row, null);
                                $field->edit = false;
                                $field->output();
                                ?>
                            </td>
                        <?php } // foreach ($this->table->getColumns() as $column) ?>
                    </tr>
                <?php } // foreach ($rows as $row) ?>
            </tbody>
        </table>

        <?php
    }

    public function outputPagination()
    {
        echo 'Page: ';
        $page_count = $this->table->get_page_count();
        $ellipsing = false;
        for ($page_num=1; $page_num<=$page_count; $page_num++) {
            if ($page_num==$this->table->page()) {
                echo " <strong>$page_num</strong> ";
                $ellipsing = false;
            } elseif ($page_num<5 || $page_num>$page_count-5) {
                $url = $this->url('/database/index/'.$this->table->getName().'?page='.$page_num);
                echo " <a href='$url' title='Go to page $page_num'>$page_num</a> ";
                $ellipsing = false;
            } elseif (!$ellipsing) {
                echo ' &hellip; ';
                $ellipsing = true;
            }
        }

//        $num_pages = $pagination['pages'];
//        $start1 = 1;
//        $end1 = min(5, $num_pages);
//        $start3 = max(1, $num_pages - 5);
//        $end3 = $num_pages;
//
//        $range1 = range($start1, $end1);

//        $page_links = array();
//        for ($page_num=1; $page_num<=$pagination['pages']; $page_num++)
//        {
//            $ellipsing = false;
//            if ($page_num<5 || $page_num>($pagination['pages']-5))
//            {
//                $page_links[] = $page_num;
//                $ellipsing = false;
//            } elseif(!$ellipsing)
//            {
//                $page_links[] = '&hellip;';
//                $ellipsing = true;
//            }
//        }
//        if ($pagination['pages'] > 1)
//        {
//            echo '<span class="pagination"><br />';
//            //for ($page_num=1; $page_num<=$pagination['pages']; $page_num++) {
//            foreach ($page_links as $page_num)
//            {
//                if ($page_num==$pagination['current_page']) {
//                    echo " $page_num ";
//                } else {
//                    $url = $this->url('database/index/'.$this->table->getName(), array('page'=>$page_num));
//                    echo " <a href='$url'>$page_num</a> ";
//                }
//            }
//            echo '</span>';
//        }
    }

}