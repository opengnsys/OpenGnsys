<?php
 
namespace Opengnsys\CoreBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Request\ParamFetcherInterface;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ApiController extends FOSRestController implements ClassResourceInterface
{
	/**
     * Constructor
     */
	public function __construct(ContainerInterface $container = null){
		$this->container = $container;
	}
	
	/**
	 * Filters criteria from $paramFetcher to be compatible with the Pager criteria.
	 *
	 * @param ParamFetcherInterface $paramFetcher
	 *
	 * @return array The filtered criteria
	 */
	protected function filterCriteria(ParamFetcherInterface $paramFetcher)
	{
		$criteria = $paramFetcher->all();
	
		unset($criteria['offset'], $criteria['limit']);
	
		foreach ($criteria as $key => $value) {
			if (null === $value) {
				unset($criteria[$key]);
			}else if($key == "fromDate" || $key == "toDate" || $key == "startDate" || $key == "endDate" ){
                $criteria[$key] = null == $value ? null : new \DateTime($value);
            }

		}
	
		return $criteria;
	}
   
}
