<?php


namespace App\Service;


use App\Entity\BEEStatusType;
use App\Entity\HoursOfAccess;
use App\Entity\Issue;
use App\Entity\IssueType;
use App\Entity\Landlord;
use App\Entity\LandlordType;
use App\Entity\Lease;
use App\Entity\LeaseDepositType;
use App\Entity\LeaseElectricityType;
use App\Entity\LeaseOtherUtilityCostCategory;
use App\Entity\LeaseType;
use App\Entity\ManagementStatus;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\UserType;
use App\Helper\LeaseHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class ReportService
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var array
     */
    public $allFields;

    /**
     * @var array
     */
    public $allOperators = [
        'LIKE' => 'Like',
        'IN' => 'IN',
        'NOT IN' => 'NOT IN',
        '<>' => '<>',
        '<' => '<',
        '>' => '>',
        '=' => '=',
        'BETWEEN' => 'Between',
    ];

    /**
     * ReportService constructor.
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em) {
        $this->em = $em;

        $this->generateAllFieldsList();
    }

    /**
     * @param $filters
     * @param $fields
     * @return array|null
     * @throws \Exception
     */
    public function getResult($filters, $fields){
        if(!empty($filters) && !empty($fields)){
            $filters = $this->generateFilters($filters);
            $fields = $this->generateFields($fields);
            $items = $this->getItems($filters, $fields);
            $items = $this->addAgentFieldsToItems($fields, $items);
            $items = $this->getItemsWithAdditionalFilters($filters, $items);

            return $this->generateResult($fields, $items);
        }

        return null;
    }

    /**
     * @param $fields
     * @param $items
     * @return array
     * @throws \Exception
     */
    private function generateResult($fields, $items){
        $resultFields = $this->generateFieldsForResult($fields);
        $resultItems = $this->generateItemsForResult($resultFields, $items);

        return [
            'fields' => $resultFields,
            'items' => $resultItems
        ];
    }

    /**
     * @param $resultFields
     * @param $items
     * @return array
     * @throws \Exception
     */
    private function generateItemsForResult($resultFields, $items){
        $resultItems = [];
        if(!empty($items)){
            foreach ($items as $item){
                $temp = [];
                foreach ($resultFields as $fieldKey=>$fieldName){
                    if(array_key_exists($fieldKey, $item)){
                        switch ($fieldKey){
                            case 'siteStatus':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    $value = $this->em->getRepository(ManagementStatus::class)->find($item[$fieldKey]);
                                }
                                $temp[$fieldKey] = ($value instanceof ManagementStatus) ? $value->getName() : $value;
                                break;
                            case 'siteStatusUpdated':
                            case 'startDate':
                            case 'endDate':
                            case 'logged':
                                if($item[$fieldKey] instanceof \DateTime){
                                    $temp[$fieldKey] = $item[$fieldKey]->format('d M Y');
                                }
                                else{
                                    $temp[$fieldKey] = $item[$fieldKey];
                                }
                                break;
                            case 'hoursOfAccess':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    $value = $this->em->getRepository(HoursOfAccess::class)->find($item[$fieldKey]);
                                }
                                $temp[$fieldKey] = ($value instanceof HoursOfAccess) ? $value->getName() : $value;
                                break;
                            case 'landlordType':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    $value = $this->em->getRepository(LandlordType::class)->find($item[$fieldKey]);
                                }
                                $temp[$fieldKey] = ($value instanceof LandlordType) ? $value->getName() : $value;
                                break;
                            case 'beeStatus':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    $value = $this->em->getRepository(BEEStatusType::class)->find($item[$fieldKey]);
                                }
                                $temp[$fieldKey] = ($value instanceof BEEStatusType) ? $value->getName() : $value;
                                break;
                            case 'vatStatus':
                            case 'renewalStatus':
                            case 'terminationClauseStatus':
                            case 'depositStatus':
                            case 'agentStatus':
                                if($item[$fieldKey] == true){
                                    $temp[$fieldKey] = 'Yes';
                                }
                                else{
                                    $temp[$fieldKey] = 'No';
                                }
                                break;
                            case 'status':
                                if($item[$fieldKey] == true){
                                    $temp[$fieldKey] = 'Open';
                                }
                                else{
                                    $temp[$fieldKey] = 'Close';
                                }
                                break;
                            case 'leaseType':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    foreach ($item[$fieldKey] as $leaseType){
                                        if($leaseType instanceof LeaseType){
                                            $value[] = $leaseType->getName();
                                        }
                                    }
                                }
                                $temp[$fieldKey] = (is_array($value)) ? implode(',', $value) : $value;
                                break;
                            case 'renewal':
                            case 'terminationClause':
                                if($item[$fieldKey] > 0){
                                    $temp[$fieldKey] = $item[$fieldKey].' days';
                                }
                                else{
                                    $temp[$fieldKey] = $item[$fieldKey];
                                }
                                break;
                            case 'electricityType':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    $value = $this->em->getRepository(LeaseElectricityType::class)->find($item[$fieldKey]);
                                }
                                $temp[$fieldKey] = ($value instanceof LeaseElectricityType) ? $value->getName() : $value;
                                break;
                            case 'otherUtilityCost':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    foreach ($item[$fieldKey] as $utilityCost){
                                        if($utilityCost instanceof LeaseOtherUtilityCostCategory){
                                            $value[] = $utilityCost->getName();
                                        }
                                    }
                                }
                                $temp[$fieldKey] = (is_array($value)) ? implode(',', $value) : $value;
                                break;
                            case 'frequencyOfLeasePayments':
                                if(!empty($item[$fieldKey])){
                                    $temp[$fieldKey] = ucfirst($item[$fieldKey]);
                                }
                                else{
                                    $temp[$fieldKey] = $item[$fieldKey];
                                }
                                break;
                            case 'annualEscalationType':
                                if($item[$fieldKey] == 'percentage'){
                                    $temp[$fieldKey] = 'Fixed Percentage';
                                }
                                elseif ($item[$fieldKey] == 'cpi'){
                                    $temp[$fieldKey] = 'CPI Linked';
                                }
                                else{
                                    $temp[$fieldKey] = $item[$fieldKey];
                                }
                                break;
                            case 'annualEscalationDate':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    switch ($item[$fieldKey]){
                                        case '12month':
                                            $value = '12 months from start of lease';
                                            break;
                                        case '1':
                                            $value = 'January';
                                            break;
                                        case '2':
                                            $value = 'February';
                                            break;
                                        case '3':
                                            $value = 'March';
                                            break;
                                        case '4':
                                            $value = 'April';
                                            break;
                                        case '5':
                                            $value = 'May';
                                            break;
                                        case '6':
                                            $value = 'June';
                                            break;
                                        case '7':
                                            $value = 'July';
                                            break;
                                        case '8':
                                            $value = 'August';
                                            break;
                                        case '9':
                                            $value = 'September';
                                            break;
                                        case '10':
                                            $value = 'October';
                                            break;
                                        case '11':
                                            $value = 'November';
                                            break;
                                        case '12':
                                            $value = 'December';
                                            break;
                                    }
                                }

                                $temp[$fieldKey] = $value;
                                break;
                            case 'depositType':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    $value = $this->em->getRepository(LeaseDepositType::class)->find($item[$fieldKey]);
                                }
                                $temp[$fieldKey] = ($value instanceof LeaseDepositType) ? $value->getName() : $value;
                                break;
                            case 'allocated':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    $value = $this->em->getRepository(User::class)->find($item[$fieldKey]);
                                }
                                $temp[$fieldKey] = ($value instanceof User) ? $value->getFirstName()." ".$value->getLastName() : $value;
                                break;
                            case 'fee':
                                $value = null;
                                if($item[$fieldKey] == 1){
                                    $value = '% of monthly lease';
                                }
                                elseif ($item[$fieldKey] == 2){
                                    $value = '% of saving';
                                }
                                elseif ($item[$fieldKey] == 3){
                                    $value = 'Fixed Monthly Fee';
                                }
                                $temp[$fieldKey] = $value;
                                break;
                            case 'feeDuration':
                                $value = null;
                                if($item[$fieldKey] == 'all'){
                                    $value = 'Duration of the lease agreement';
                                }
                                elseif ($item[$fieldKey] > 0){
                                    $value = $item[$fieldKey].' months';
                                }
                                $temp[$fieldKey] = $value;
                                break;
                            case 'feeEscalation':
                                $value = null;
                                if($item[$fieldKey] == 1){
                                    $value = 'No Escalation';
                                }
                                elseif ($item[$fieldKey] == 2){
                                    $value = 'As per new lease terms';
                                }
                                $temp[$fieldKey] = $value;
                                break;
                            case 'issueType':
                                $value = null;
                                if(!empty($item[$fieldKey])){
                                    $value = $this->em->getRepository(IssueType::class)->find($item[$fieldKey]);
                                }
                                $temp[$fieldKey] = ($value instanceof IssueType) ? $value->getName() : $value;
                                break;

                            default:
                                $temp[$fieldKey] = $item[$fieldKey];
                                break;
                        }
                    }
                }
                $resultItems[] = $temp;
            }
        }
        return $resultItems;
    }

    /**
     * @param $fields
     * @return array
     */
    private function generateFieldsForResult($fields){
        $resultFields = [];
        foreach ($fields as $fieldType=>$field){
            if(array_key_exists($fieldType, $this->allFields)){
                foreach ($this->allFields[$fieldType] as $allField){
                    $i = array_search($allField['key'], $field);
                    if($i !== false){
                        if(
                            $allField['key'] == 'type' || $allField['key'] == 'status' || $allField['key'] == 'siteStatus'
                            || $allField['key'] == 'address' || $allField['key'] == 'address1' || $allField['key'] == 'address2'
                        ){
                            $resultFields[$allField['key']] = ucfirst($fieldType)." ".$allField['label'];
                        }
                        elseif ($allField['key'] == 'name' || $allField['key'] == 'number'){
                            $resultFields[$fieldType."_".$allField['key']] = ucfirst($fieldType)." ".$allField['label'];
                        }
                        else{
                            $resultFields[$allField['key']] = $allField['label'];
                        }
                    }
                }
            }
        }
        return $resultFields;
    }

    /**
     * @param $fields
     * @param $items
     * @return array
     * @throws \Exception
     */
    private function addAgentFieldsToItems($fields, $items){
        $newItems = [];
        foreach ($items as $item){
            if(isset($fields['agent']) && !empty($fields['agent']) && !empty($items)){
                foreach ($fields['agent'] as $fieldName){
                    switch ($fieldName){
                        case 'agentSaving':
                            $value = 0;
                            if(isset($item['agentID']) && !empty($item['agentID'])){
                                $lease = $this->em->getRepository(Lease::class)->find($item['agentID']);
                                if($lease instanceof Lease){
                                    $value = LeaseHelper::getAgentSaving($this->em, $lease);
                                }
                            }
                            $item[$fieldName] = $value;
                            break;
                        case 'agentSavingPercentage':
                            $value = 0;
                            if(isset($item['agentID']) && !empty($item['agentID'])){
                                $lease = $this->em->getRepository(Lease::class)->find($item['agentID']);
                                if($lease instanceof Lease){
                                    $value = LeaseHelper::getAgentSavingPercentage($this->em, $lease);
                                }
                            }
                            $item[$fieldName] = $value;
                            break;
                        case 'agentBilling':
                            $value = 0;
                            if(isset($item['agentID']) && !empty($item['agentID'])){
                                $lease = $this->em->getRepository(Lease::class)->find($item['agentID']);
                                if($lease instanceof Lease){
                                    $value = LeaseHelper::getAgentBilling($this->em, $lease);
                                }
                            }
                            $item[$fieldName] = $value;
                            break;
                        case 'escalationSavingPercentage':
                            $value = 0;
                            if(isset($item['agentID']) && !empty($item['agentID'])){
                                $lease = $this->em->getRepository(Lease::class)->find($item['agentID']);
                                if($lease instanceof Lease){
                                    $value = LeaseHelper::getEscalationSaving($this->em, $lease);
                                }
                            }
                            $item[$fieldName] = $value;
                            break;
                    }
                }
            }
            if(!isset($item['agentSaving']) || $item['agentSaving'] >= 0){
                $newItems[] = $item;
            }
        }

        return $newItems;
    }

    /**
     * @param $filters
     * @param $items
     * @return array
     * @throws \Exception
     */
    private function getItemsWithAdditionalFilters($filters, $items){
        $newItems = [];
        if(!empty($items)){
            foreach ($items as $item){
                $condition1 = $this->additionalFilterLeaseType($filters, $item);
                $condition2 = $this->additionalFilterOtherUtilityCost($filters, $item);
                $condition3 = $this->additionalFilterAgentFields($filters, $item);

                if($condition1 && $condition2 && $condition3){
                    $newItems[] = $item;
                }
            }
        }

        return $newItems;
    }

    /**
     * @param $filters
     * @param $item
     * @return bool
     */
    private function additionalFilterLeaseType($filters, $item){
        $condition = true;
        if(isset($filters['lease']) && !empty($filters['lease'])) {
            if (!empty(array_keys(array_column($filters['lease'], 'name'), 'leaseType'))) {
                $i = array_keys(array_column($filters['lease'], 'name'), 'leaseType');
                $i = $i[0];
                if (
                    isset($filters['lease'][$i]['operator']) && in_array($filters['lease'][$i]['operator'], ['IN', 'NOT IN'])
                    && isset($filters['lease'][$i]['value']) && !empty($filters['lease'][$i]['value'])
                ) {
                    if ($filters['lease'][$i]['operator'] == 'IN') {
                        $condition = false;
                    } else {
                        $condition = true;
                    }
                    if (isset($item['leaseType']) && !empty($item['leaseType'])) {
                        foreach ($item['leaseType'] as $leaseType) {
                            if ($leaseType instanceof LeaseType) {
                                if (in_array($leaseType->getId(), $filters['lease'][$i]['value'])) {
                                    if ($filters['lease'][$i]['operator'] == 'IN') {
                                        $condition = true;
                                    } else {
                                        $condition = false;
                                    }
                                }
                            }
                        }
                    }
                    elseif(isset($item['leaseID']) && !empty($item['leaseID'])){
                        $lease = $this->em->getRepository(Lease::class)->find($item['leaseID']);
                        if($lease instanceof Lease){
                            foreach ($lease->getType() as $leaseType) {
                                if ($leaseType instanceof LeaseType) {
                                    if (in_array($leaseType->getId(), $filters['lease'][$i]['value'])) {
                                        if ($filters['lease'][$i]['operator'] == 'IN') {
                                            $condition = true;
                                        } else {
                                            $condition = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $condition;
    }

    /**
     * @param $filters
     * @param $item
     * @return bool
     */
    private function additionalFilterOtherUtilityCost($filters, $item){
        $condition = true;
        if(isset($filters['lease']) && !empty($filters['lease'])) {
            if(!empty(array_keys(array_column($filters['lease'], 'name'), 'otherUtilityCost'))){
                $i = array_keys(array_column($filters['lease'], 'name'), 'otherUtilityCost');
                $i = $i[0];
                if(
                    isset($filters['lease'][$i]['operator']) && in_array($filters['lease'][$i]['operator'], ['IN', 'NOT IN'])
                    && isset($filters['lease'][$i]['value']) && !empty($filters['lease'][$i]['value'])
                ){
                    if($filters['lease'][$i]['operator'] == 'IN'){
                        $condition = false;
                    }
                    else{
                        $condition = true;
                    }
                    if(isset($item['otherUtilityCost']) && !empty($item['otherUtilityCost'])){
                        foreach ($item['otherUtilityCost'] as $otherUtilityCost){
                            if($otherUtilityCost instanceof LeaseOtherUtilityCostCategory){
                                if(in_array($otherUtilityCost->getId(), $filters['lease'][$i]['value'])){
                                    if($filters['lease'][$i]['operator'] == 'IN'){
                                        $condition = true;
                                    }
                                    else{
                                        $condition = false;
                                    }
                                }
                            }
                        }
                    }
                    elseif(isset($item['leaseID']) && !empty($item['leaseID'])){
                        $lease = $this->em->getRepository(Lease::class)->find($item['leaseID']);
                        if($lease instanceof Lease){
                            foreach ($lease->getOtherUtilityCost() as $otherUtilityCost){
                                if($otherUtilityCost instanceof LeaseOtherUtilityCostCategory){
                                    if(in_array($otherUtilityCost->getId(), $filters['lease'][$i]['value'])){
                                        if($filters['lease'][$i]['operator'] == 'IN'){
                                            $condition = true;
                                        }
                                        else{
                                            $condition = false;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return $condition;
    }

    /**
     * @param $filters
     * @param $item
     * @return bool
     * @throws \Exception
     */
    private function additionalFilterAgentFields($filters, $item){
        $condition = true;
        $agentFields = ['agentSaving', 'agentSavingPercentage', 'agentBilling', 'escalationSavingPercentage'];
        if(isset($filters['agent']) && !empty($filters['agent'])){
            foreach ($agentFields as $agentField){
                if(!empty(array_keys(array_column($filters['agent'], 'name'), $agentField))){
                    $i = array_keys(array_column($filters['agent'], 'name'), $agentField);
                    $i = $i[0];
                    if(
                        isset($filters['agent'][$i]['operator']) && isset($filters['agent'][$i]['value'])
                        && (!empty($filters['agent'][$i]['value']) || $filters['agent'][$i]['value'] >= 0)
                    ){
                        $condition = false;
                        if(isset($item[$agentField])){
                            if($filters['agent'][$i]['operator'] == '<>'){
                                if($item[$agentField] != $filters['agent'][$i]['value']){
                                    $condition = true;
                                }
                            }
                            elseif($filters['agent'][$i]['operator'] == '<'){
                                if($item[$agentField] < $filters['agent'][$i]['value']){
                                    $condition = true;
                                }
                            }
                            elseif($filters['agent'][$i]['operator'] == '>'){
                                if($item[$agentField] > $filters['agent'][$i]['value']){
                                    $condition = true;
                                }
                            }
                            elseif($filters['agent'][$i]['operator'] == '='){
                                if($item[$agentField] == $filters['agent'][$i]['value']){
                                    $condition = true;
                                }
                            }
                            elseif($filters['agent'][$i]['operator'] == 'BETWEEN'){
                                if(isset($filters['agent'][$i]['value']['from']) && isset($filters['agent'][$i]['value']['to'])){
                                    if($item[$agentField] > $filters['agent'][$i]['value']['from'] && $item[$agentField] < $filters['agent'][$i]['value']['to']){
                                        $condition = true;
                                    }
                                }
                            }
                        }
                        elseif(isset($item['agentID']) && !empty($item['agentID'])){
                            $lease = $this->em->getRepository(Lease::class)->find($item['agentID']);
                            if($lease instanceof Lease){
                                switch ($agentField){
                                    case 'agentSaving':
                                        $value = LeaseHelper::getAgentSaving($this->em, $lease);
                                        break;
                                    case 'agentSavingPercentage':
                                        $value = LeaseHelper::getAgentSavingPercentage($this->em, $lease);
                                        break;
                                    case 'agentBilling':
                                        $value = LeaseHelper::getAgentBilling($this->em, $lease);
                                        break;
                                    case 'escalationSavingPercentage':
                                        $value = LeaseHelper::getEscalationSaving($this->em, $lease);
                                        break;
                                }
                                if(isset($value)){
                                    if($filters['agent'][$i]['operator'] == '<>'){
                                        if($value != $filters['agent'][$i]['value']){
                                            $condition = true;
                                        }
                                    }
                                    elseif($filters['agent'][$i]['operator'] == '<'){
                                        if($value < $filters['agent'][$i]['value']){
                                            $condition = true;
                                        }
                                    }
                                    elseif($filters['agent'][$i]['operator'] == '>'){
                                        if($value > $filters['agent'][$i]['value']){
                                            $condition = true;
                                        }
                                    }
                                    elseif($filters['agent'][$i]['operator'] == '='){
                                        if($value == $filters['agent'][$i]['value']){
                                            $condition = true;
                                        }
                                    }
                                    elseif($filters['agent'][$i]['operator'] == 'BETWEEN'){
                                        if(isset($filters['agent'][$i]['value']['from']) && isset($filters['agent'][$i]['value']['to'])){
                                            if($value > $filters['agent'][$i]['value']['from'] && $value < $filters['agent'][$i]['value']['to']){
                                                $condition = true;
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        if($condition == false){
                            return $condition;
                        }
                    }
                }
            }
        }

        return $condition;
    }

    /**
     * @param $filters
     * @param $fields
     * @return mixed
     * @throws \Exception
     */
    private function getItems($filters, $fields){
        $qb = $this->em->createQueryBuilder();
        $qb = $this->addTableToQuery($qb, $filters, $fields);
        $qb = $this->addSelectToQuery($qb, $filters, $fields);
        $qb = $this->addFilterToQuery($qb, $filters);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param $filters
     * @return QueryBuilder
     * @throws \Exception
     */
    private function addFilterToQuery(QueryBuilder $qb, $filters){
        foreach ($filters as $filterType=>$filterItem){
            $tableName = $filterType;
            if($tableName == 'agent'){
                $tableName = 'lease';
            }
            foreach ($filterItem as $filter){
                if(
                    isset($filter['type']) && !empty($filter['type']) && isset($filter['name']) && !empty($filter['name'])
                    && isset($filter['operator']) && !empty($filter['operator']) && isset($filter['fieldType']) && !empty($filter['fieldType'])
                    && isset($filter['value']) && ((is_array($filter['value']) && !empty($filter['value'])) || $filter['value'] != '')
                ){
                    if(
                        $filter['name'] != 'agentSaving' && $filter['name'] != 'agentSavingPercentage' && $filter['name'] != 'agentBilling'
                        && $filter['name'] != 'escalationSavingPercentage' && $filter['name'] != 'leaseType' && $filter['name'] != 'otherUtilityCost'
                    ){
                        if($filter['name'] == 'landlordType' || $filter['name'] == 'leaseType' || $filter['name'] == 'issueType'){
                            $filter['name'] = 'type';
                        }

                        if($filter['operator'] == 'IN' || $filter['operator'] == 'NOT IN'){
                            if($filter['name'] == 'frequencyOfLeasePayments' || $filter['name'] == 'annualEscalationType' || $filter['name'] == 'annualEscalationDate'){
                                $inFilter = '';
                                foreach ($filter['value'] as $v){
                                    if(is_numeric($v)){
                                        $inFilter .= $v.",";
                                    }
                                    else{
                                        $inFilter .= "'".$v."',";
                                    }
                                }
                                $inFilter = rtrim($inFilter, ",");
                                $qb->andWhere($tableName.".".$filter['name']." ".$filter['operator']." (".$inFilter.")");
                            }
                            else{
                                $qb->andWhere($tableName.'.'.$filter['name'].' '.$filter['operator'].' ('.implode(",", $filter['value']).')');
                            }
                        }
                        elseif ($filter['operator'] == 'BETWEEN'){
                            if(
                                isset($filter['value']['from']) && ($filter['value']['from'] != '' || (is_array($filter['value']['from']) && !empty($filter['value']['from'])))
                                && isset($filter['value']['to']) && ($filter['value']['to'] != '' || (is_array($filter['value']['from']) && !empty($filter['value']['to'])))
                            ){
                                if ($filter['fieldType'] == 'date'){
                                    $from = new \DateTime($filter['value']['from']);
                                    $to = new \DateTime($filter['value']['to']);
                                    $qb->andWhere("DATE_FORMAT(".$tableName.".".$filter['name'].", '%Y-%m-%d') ".$filter['operator']." '".$from->format('Y-m-d')."' AND '".$to->format('Y-m-d')."'");
                                }
                                elseif ($filter['fieldType'] == 'number'){
                                    $qb->andWhere($tableName.".".$filter['name']." ".$filter['operator']." ".$filter['value']['from']." AND ".$filter['value']['to']);
                                }
                                else{
                                    $qb->andWhere($tableName.".".$filter['name']." ".$filter['operator']." '".$filter['value']['from']."' AND '".$filter['value']['to']."'");
                                }
                            }

                        }
                        elseif ($filter['operator'] == 'LIKE'){
                            $qb->andWhere($tableName.".".$filter['name']." ".$filter['operator']." '%".$filter['value']."%'");
                        }
                        else{
                            if ($filter['fieldType'] == 'date') {
                                $date = new \DateTime($filter['value']);
                                $qb->andWhere("DATE_FORMAT(".$tableName.".".$filter['name'].", '%Y-%m-%d') ".$filter['operator']." '".$date->format('Y-m-d')."'");
                            }
                            elseif ($filter['fieldType'] == 'number'){
                                $qb->andWhere($tableName.".".$filter['name']." ".$filter['operator']." ".$filter['value']);
                            }
                            else{
                                $qb->andWhere($tableName.".".$filter['name']." ".$filter['operator']." '".$filter['value']."'");
                            }
                        }
                    }
                }
            }
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param $filters
     * @param $fields
     * @return QueryBuilder
     */
    private function addSelectToQuery(QueryBuilder $qb, $filters, $fields){
        $fieldsStr = '';
        $allTables = $this->getAllUseTables($filters, $fields);
        foreach ($allTables as $table){
            if($table != 'agent'){
                $fieldsStr .= $table.'.id as '.$table.'ID,';
            }
            else{
                $fieldsStr .= 'lease.id as agentID,';
            }
        }
        foreach ($fields as $fieldType=>$field){
            foreach ($field as $fieldName){
                if(
                    ($fieldType == 'site' && ($fieldName == 'siteStatus' || $fieldName == 'hoursOfAccess'))
                    || ($fieldType == 'landlord' && $fieldName == 'beeStatus')
                    || ($fieldType == 'lease' && ($fieldName == 'electricityType' || $fieldName == 'depositType'))
                ){
                    $fieldsStr .= "IDENTITY(".$fieldType.".".$fieldName.") as ".$fieldName.",";
                }
                elseif(
                    ($fieldType == 'site' && ($fieldName == 'name' || $fieldName == 'number'))
                    || ($fieldType == 'landlord' && ($fieldName == 'name' || $fieldName == 'number'))
                ){
                    $fieldsStr .= $fieldType.'.'.$fieldName.' as '.$fieldType.'_'.$fieldName.',';
                }
                elseif($fieldType == 'landlord' && $fieldName == 'landlordType'){
                    $fieldsStr .= 'IDENTITY(landlord.type) as landlordType,';
                }
                elseif($fieldType == 'lease' && $fieldName == 'leaseType'){
                    $fieldsStr .= 'lease.type as leaseType,';
                }
                elseif ($fieldType == 'agent'){
                    if($fieldName == 'allocated'){
                        $fieldsStr .= "IDENTITY(lease.allocated) as allocated,";
                    }
                    elseif($fieldName != 'agentSaving' && $fieldName != 'agentSavingPercentage' && $fieldName != 'agentBilling' && $fieldName != 'escalationSavingPercentage'){
                        $fieldsStr .= 'lease.'.$fieldName.',';
                    }
                }
                elseif ($fieldType == 'issue' && $fieldName == 'issueType'){
                    $fieldsStr .= "IDENTITY(issue.type) as issueType,";
                }
                else{
                    $fieldsStr .= $fieldType.'.'.$fieldName.',';
                }
            }
        }
        $fieldsStr = rtrim($fieldsStr, ',');
        $qb->select($fieldsStr)->distinct(true);

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param $filters
     * @param $fields
     * @return QueryBuilder
     */
    private function addTableToQuery(QueryBuilder $qb, $filters, $fields){
        $fieldTypes = $this->getAllUseTables($filters, $fields);
        if(count($fieldTypes) == 1){
            if(in_array('site', $fieldTypes)){
                $qb->from(Site::class, 'site');
            }
            if(in_array('landlord', $fieldTypes)){
                $qb->from(Landlord::class, 'landlord');
            }
            if(in_array('lease', $fieldTypes)){
                $qb->from(Lease::class, 'lease');
            }
            if(in_array('agent', $fieldTypes)){
                $qb
                    ->from(Lease::class, 'lease')
                    ->andWhere('lease.agentStatus = 1');
            }
            if(in_array('issue', $fieldTypes)){
                $qb->from(Issue::class, 'issue');
            }
        }
        elseif (count($fieldTypes) == 2){
            if(in_array('site', $fieldTypes) && in_array('landlord', $fieldTypes)){
                $qb
                    ->from(Site::class, 'site')
                    ->from(Landlord::class, 'landlord')
                    ->from(Lease::class, 'lease')
                    ->andWhere('lease.site = site.id')
                    ->andWhere('lease.landlord = landlord.id');
            }
            if(in_array('site', $fieldTypes) && in_array('lease', $fieldTypes)){
                $qb
                    ->from(Site::class, 'site')
                    ->from(Lease::class, 'lease')
                    ->andWhere('lease.site = site.id');
            }
            if(in_array('site', $fieldTypes) && in_array('agent', $fieldTypes)){
                $qb
                    ->from(Site::class, 'site')
                    ->from(Lease::class, 'lease')
                    ->andWhere('lease.site = site.id')
                    ->andWhere('lease.agentStatus = 1');
            }
            if(in_array('site', $fieldTypes) && in_array('issue', $fieldTypes)){
                $qb
                    ->from(Site::class, 'site')
                    ->from(Issue::class, 'issue')
                    ->andWhere('issue.site = site.id');
            }
            if(in_array('landlord', $fieldTypes) && in_array('lease', $fieldTypes)){
                $qb
                    ->from(Landlord::class, 'landlord')
                    ->from(Lease::class, 'lease')
                    ->andWhere('lease.landlord = landlord.id');
            }
            if(in_array('landlord', $fieldTypes) && in_array('agent', $fieldTypes)){
                $qb
                    ->from(Landlord::class, 'landlord')
                    ->from(Lease::class, 'lease')
                    ->andWhere('lease.landlord = landlord.id')
                    ->andWhere('lease.agentStatus = 1');
            }
            if(in_array('landlord', $fieldTypes) && in_array('issue', $fieldTypes)){
                $qb
                    ->from(Landlord::class, 'landlord')
                    ->from(Issue::class, 'issue')
                    ->from(Lease::class, 'lease')
                    ->from(Site::class, 'site')
                    ->andWhere('issue.site = site.id')
                    ->andWhere('site.id = lease.site')
                    ->andWhere('lease.landlord = landlord.id');
            }
            if(in_array('lease', $fieldTypes) && in_array('agent', $fieldTypes)){
                $qb
                    ->from(Lease::class, 'lease')
                    ->andWhere('lease.agentStatus = 1');
            }
            if(in_array('lease', $fieldTypes) && in_array('issue', $fieldTypes)){
                $qb
                    ->from(Lease::class, 'lease')
                    ->from(Site::class, 'site')
                    ->from(Issue::class, 'issue')
                    ->andWhere('lease.site = site.id')
                    ->andWhere('site.id = issue.site');
            }
            if(in_array('agent', $fieldTypes) && in_array('issue', $fieldTypes)){
                $qb
                    ->from(Lease::class, 'lease')
                    ->from(Site::class, 'site')
                    ->from(Issue::class, 'issue')
                    ->andWhere('lease.site = site.id')
                    ->andWhere('site.id = issue.site')
                    ->andWhere('lease.agentStatus = 1');
            }
        }
        else{
            $qb
                ->from(Site::class, 'site')
                ->from(Landlord::class, 'landlord')
                ->from(Lease::class, 'lease')
                ->andWhere('site.id = lease.site')
                ->andWhere('landlord.id = lease.landlord');
            if(in_array('agent', $fieldTypes)){
                $qb->andWhere('lease.agentStatus = 1');
            }
            if(in_array('issue', $fieldTypes)){
                $qb
                    ->from(Issue::class, 'issue')
                    ->andWhere('issue.site = site.id');
            }
        }

        return $qb;
    }

    /**
     * @param $filters
     * @param $fields
     * @return array
     */
    private function getAllUseTables($filters, $fields){

        return array_unique(array_merge(array_keys($filters),array_keys($fields)), SORT_REGULAR);
    }

    /**
     * @param $items
     * @return array
     */
    private function generateFilters($items) {
        $filters = [];
        if(!empty($items)){
            foreach ($items as $item){
                if(isset($item['type']) && !empty($item['type'])){
                    $filters[$item['type']][] = $item;
                }
            }
        }

        return $filters;
    }

    /**
     * @param $items
     * @return array
     */
    private function generateFields($items){
        $fields = [];
        if(!empty($items)){
            foreach ($items as $type=>$item){
                $fields[$type] = array_keys($item);
            }
        }

        return $fields;
    }

    /**
     * @return void
     */
    private function generateAllFieldsList(){
        $this->generateSiteFieldsList();
        $this->generateLandlordFieldsList();
        $this->generateLeaseFieldsList();
        $this->generateAgentFieldsList();
        $this->generateIssueFieldsList();
    }

    /**
     * @return void
     */
    private function generateSiteFieldsList(){
        $siteStatusesResult = $this->em->getRepository(ManagementStatus::class)->findAll();
        $siteStatuses = [];
        foreach ($siteStatusesResult as $siteStatus){
            if($siteStatus instanceof ManagementStatus){
                $siteStatuses[] = [
                    'id' => $siteStatus->getId(),
                    'name' => $siteStatus->getName()
                ];
            }
        }

        $hoursOfAccessResult = $this->em->getRepository(HoursOfAccess::class)->findAll();
        $hoursOfAccess = [];
        foreach ($hoursOfAccessResult as $hoursOfAccessItem){
            if($hoursOfAccessItem instanceof HoursOfAccess){
                $hoursOfAccess[] = [
                    'id' => $hoursOfAccessItem->getId(),
                    'name' => $hoursOfAccessItem->getName()
                ];
            }
        }

        $this->allFields['site'] = [
            [
                'key' => 'number',
                'label' => 'Number',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'name',
                'label' => 'Name',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'address',
                'label' => 'Address',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'siteStatus',
                'label' => 'Status',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $siteStatuses
            ],
            [
                'key' => 'siteStatusUpdated',
                'label' => 'Status Update',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'date'
            ],
            [
                'key' => 'erf',
                'label' => 'ERF',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'hoursOfAccess',
                'label' => 'Hours of Access',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $hoursOfAccess
            ]
        ];
    }

    /**
     * @return void
     */
    private function generateLandlordFieldsList(){
        $landlordTypeResult = $this->em->getRepository(LandlordType::class)->findAll();
        $landlordTypes = [];
        foreach ($landlordTypeResult as $landlordType){
            if($landlordType instanceof LandlordType){
                $landlordTypes[] = [
                    'id' => $landlordType->getId(),
                    'name' => $landlordType->getName()
                ];
            }
        }

        $beeStatusesResult = $this->em->getRepository(BEEStatusType::class)->findAll();
        $beeStatuses = [];
        foreach ($beeStatusesResult as $beeStatus){
            if($beeStatus instanceof BEEStatusType){
                $beeStatuses[] = [
                    'id' => $beeStatus->getId(),
                    'name' => $beeStatus->getName()
                ];
            }
        }

        $this->allFields['landlord'] = [
            [
                'key' => 'name',
                'label' => 'Name',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'landlordType',
                'label' => 'Type',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $landlordTypes
            ],
            [
                'key' => 'number',
                'label' => 'Number',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'address1',
                'label' => 'Address',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'address2',
                'label' => 'Address 2',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'beeStatus',
                'label' => 'B-BBEE Status',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $beeStatuses
            ],
            [
                'key' => 'vatStatus',
                'label' => 'VAT Status',
                'operators' => ['='],
                'type' => 'boolean'
            ],
            [
                'key' => 'vatNumber',
                'label' => 'VAT Number',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
        ];
    }

    /**
     * @return void
     */
    private function generateLeaseFieldsList(){
        $leaseTypesResult = $this->em->getRepository(LeaseType::class)->findAll();
        $leaseTypes = [];
        foreach ($leaseTypesResult as $leaseType){
            if($leaseType instanceof LeaseType){
                $leaseTypes[] = [
                    'id' => $leaseType->getId(),
                    'name' => $leaseType->getName()
                ];
            }
        }

        $electricityTypesResult = $this->em->getRepository(LeaseElectricityType::class)->findAll();
        $electricityTypes = [];
        foreach ($electricityTypesResult as $electricityType){
            if($electricityType instanceof LeaseElectricityType){
                $electricityTypes[] = [
                    'id' => $electricityType->getId(),
                    'name' => $electricityType->getName()
                ];
            }
        }

        $otherUtilityCostsResult = $this->em->getRepository(LeaseOtherUtilityCostCategory::class)->findAll();
        $otherUtilityCosts = [];
        foreach ($otherUtilityCostsResult as $otherUtilityCost){
            if($otherUtilityCost instanceof LeaseOtherUtilityCostCategory){
                $otherUtilityCosts[] = [
                    'id' => $otherUtilityCost->getId(),
                    'name' => $otherUtilityCost->getName()
                ];
            }
        }

        $depositTypesResult = $this->em->getRepository(LeaseDepositType::class)->findAll();
        $depositTypes = [];
        foreach ($depositTypesResult as $depositType){
            if($depositType instanceof LeaseDepositType){
                $depositTypes[] = [
                    'id' => $depositType->getId(),
                    'name' => $depositType->getName()
                ];
            }
        }

        $this->allFields['lease'] = [
            [
                'key' => 'leaseType',
                'label' => 'Type',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $leaseTypes
            ],
            [
                'key' => 'sqm',
                'label' => 'Size',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'term',
                'label' => 'Term',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'startDate',
                'label' => 'Start Date',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'date'
            ],
            [
                'key' => 'endDate',
                'label' => 'End Date',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'date'
            ],
            [
                'key' => 'renewalStatus',
                'label' => 'Renewal Status',
                'operators' => ['='],
                'type' => 'boolean'
            ],
            [
                'key' => 'renewal',
                'label' => 'Renewal',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => [
                    [
                        'id' => 30,
                        'name' => '30 days'
                    ],
                    [
                        'id' => 60,
                        'name' => '60 days'
                    ],
                    [
                        'id' => 90,
                        'name' => '90 days'
                    ],
                    [
                        'id' => 180,
                        'name' => '180 days'
                    ],
                    [
                        'id' => 360,
                        'name' => '360 days'
                    ],
                ]
            ],
            [
                'key' => 'terminationClauseStatus',
                'label' => 'Termination Clause Status',
                'operators' => ['='],
                'type' => 'boolean'
            ],
            [
                'key' => 'terminationClause',
                'label' => 'Termination Clause',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => [
                    [
                        'id' => 30,
                        'name' => '30 days'
                    ],
                    [
                        'id' => 60,
                        'name' => '60 days'
                    ],
                    [
                        'id' => 90,
                        'name' => '90 days'
                    ],
                    [
                        'id' => 180,
                        'name' => '180 days'
                    ],
                    [
                        'id' => 360,
                        'name' => '360 days'
                    ],
                ]
            ],
            [
                'key' => 'electricityType',
                'label' => 'Electricity Type',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $electricityTypes
            ],
            [
                'key' => 'electricityFixed',
                'label' => 'Electricity Fixed',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'otherUtilityCost',
                'label' => 'Other Utility Cost',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $otherUtilityCosts
            ],
            [
                'key' => 'frequencyOfLeasePayments',
                'label' => 'Frequency of Payments',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => [
                    [
                        'id' => 'monthly',
                        'name' => 'Monthly'
                    ],
                    [
                        'id' => 'annually',
                        'name' => 'Annually'
                    ],
                ]
            ],
            [
                'key' => 'annualEscalationType',
                'label' => 'Annual Escalation Type',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => [
                    [
                        'id' => 'percentage',
                        'name' => 'Fixed Percentage'
                    ],
                    [
                        'id' => 'cpi',
                        'name' => 'CPI Linked'
                    ],
                ]
            ],
            [
                'key' => 'annualEscalation',
                'label' => 'Annual Escalation',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'annualEscalationCpi',
                'label' => 'Annual Escalation CPI',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'annualEscalationDate',
                'label' => 'First Escalation Month',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => [
                    [
                        'id' => '12month',
                        'name' => '12 months from start of lease'
                    ],
                    [
                        'id' => '1',
                        'name' => 'January'
                    ],
                    [
                        'id' => '2',
                        'name' => 'February'
                    ],
                    [
                        'id' => '3',
                        'name' => 'March'
                    ],
                    [
                        'id' => '4',
                        'name' => 'April'
                    ],
                    [
                        'id' => '5',
                        'name' => 'May'
                    ],
                    [
                        'id' => '6',
                        'name' => 'June'
                    ],
                    [
                        'id' => '7',
                        'name' => 'July'
                    ],
                    [
                        'id' => '8',
                        'name' => 'August'
                    ],
                    [
                        'id' => '9',
                        'name' => 'September'
                    ],
                    [
                        'id' => '10',
                        'name' => 'October'
                    ],
                    [
                        'id' => '11',
                        'name' => 'November'
                    ],
                    [
                        'id' => '12',
                        'name' => 'December'
                    ],
                ]
            ],
            [
                'key' => 'depositStatus',
                'label' => 'Deposit Status',
                'operators' => ['='],
                'type' => 'boolean'
            ],
            [
                'key' => 'depositType',
                'label' => 'Deposit Type',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $depositTypes
            ],
            [
                'key' => 'depositAmount',
                'label' => 'Deposit Amount',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'agentStatus',
                'label' => 'Agent Status',
                'operators' => ['='],
                'type' => 'boolean'
            ],
        ];
    }

    /**
     * @return void
     */
    private function generateAgentFieldsList(){
        $agentType = $this->em->getRepository(UserType::class)->findOneBy(['name'=>'Agent']);
        $agentUsers = [];
        if($agentType instanceof UserType){
            $agentUsersResult = $this->em->getRepository(User::class)->findBy(['type'=>$agentType]);
            foreach ($agentUsersResult as $user){
                if($user instanceof User){
                    $agentUsers[] = [
                        'id' => $user->getId(),
                        'name' => $user->getFirstName()." ".$user->getLastName()
                    ];
                }
            }
        }

        $this->allFields['agent'] = [
            [
                'key' => 'allocated',
                'label' => 'Allocated to',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $agentUsers
            ],
            [
                'key' => 'fee',
                'label' => 'Fee',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => [
                    [
                        'id' => 1,
                        'name' => '% of Monthly Lease'
                    ],
                    [
                        'id' => 2,
                        'name' => '% of Saving'
                    ],
                    [
                        'id' => 3,
                        'name' => 'Fixed Monthly Fee'
                    ],
                ]
            ],
            [
                'key' => 'feeValue',
                'label' => 'Fee Amount',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'feeDuration',
                'label' => 'Fee Duration',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'feeEscalation',
                'label' => 'Fee Escalation',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => [
                    [
                        'id' => 1,
                        'name' => 'No Escalation'
                    ],
                    [
                        'id' => 2,
                        'name' => 'As per new lease terms'
                    ],
                ]
            ],
            [
                'key' => 'proposedLease',
                'label' => 'Proposed Lease',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'targetRenewalRental',
                'label' => 'Target Renewal Rental',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'targetRenewalEscalation',
                'label' => 'Target Renewal Escalation',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'agentSaving',
                'label' => 'Agent Saving',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'agentSavingPercentage',
                'label' => 'Agent Saving %',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'agentBilling',
                'label' => 'Agent Billing',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
            [
                'key' => 'escalationSavingPercentage',
                'label' => 'Escalation Saving %',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'number'
            ],
        ];
    }

    /**
     * @return void
     */
    private function generateIssueFieldsList(){
        $issueTypesResult = $this->em->getRepository(IssueType::class)->findAll();
        $issueTypes = [];
        foreach ($issueTypesResult as $issueType){
            if($issueType instanceof IssueType){
                $issueTypes[] = [
                    'id' => $issueType->getId(),
                    'name' => $issueType->getName()
                ];
            }
        }

        $this->allFields['issue'] = [
            [
                'key' => 'issueType',
                'label' => 'Type',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => $issueTypes
            ],
            [
                'key' => 'logged',
                'label' => 'Data logged',
                'operators' => ['<>', '<', '>', '=', 'BETWEEN'],
                'type' => 'date'
            ],
            [
                'key' => 'details',
                'label' => 'Details',
                'operators' => ['LIKE'],
                'type' => 'text'
            ],
            [
                'key' => 'status',
                'label' => 'Status',
                'operators' => ['IN', 'NOT IN'],
                'type' => 'list',
                'values' => [
                    [
                        'id' => 1,
                        'name' => 'Open'
                    ],
                    [
                        'id' => 0,
                        'name' => 'Close'
                    ]
                ]
            ],
        ];
    }
}
